package services

import (
	"context"
	"fmt"
	"log"
	"sync"
	"time"
)

// PoolEntry represents a pre-warmed container in the pool
type PoolEntry struct {
	ContainerID string
	LabSlug     string
	Port        int
	Status      string // "ready", "allocated", "expired"
	CreatedAt   time.Time
	ExpiresAt   time.Time
}

// ContainerPool manages a pool of pre-warmed containers
type ContainerPool struct {
	mu        sync.RWMutex
	manager   *ContainerManager
	entries   map[string]*PoolEntry // containerID -> entry
	allocMap  map[string]string     // labSlug -> containerID (currently allocated)
	poolSize  int
	timeout   time.Duration
	stopCh    chan struct{}
}

// NewContainerPool creates a new container pool
func NewContainerPool(manager *ContainerManager, poolSize int, timeout time.Duration) *ContainerPool {
	return &ContainerPool{
		manager:  manager,
		entries:  make(map[string]*PoolEntry),
		allocMap: make(map[string]string),
		poolSize: poolSize,
		timeout:  timeout,
		stopCh:   make(chan struct{}),
	}
}

// Start begins the pool management loop
func (p *ContainerPool) Start(ctx context.Context) error {
	log.Println("[pool] starting container pool manager")

	// Initial warm-up
	if err := p.warmUp(ctx); err != nil {
		log.Printf("[pool] warm-up failed: %v", err)
	}

	// Background maintenance
	go p.maintenanceLoop(ctx)

	return nil
}

// Allocate gets a container for a specific lab
func (p *ContainerPool) Allocate(ctx context.Context, labSlug string, image string, port int) (*PoolEntry, error) {
	p.mu.Lock()
	defer p.mu.Unlock()

	// Check if already allocated
	if existing, ok := p.allocMap[labSlug]; ok {
		if entry, exists := p.entries[existing]; exists && entry.Status == "allocated" {
			return entry, nil
		}
	}

	// Look for a ready container in the pool
	for _, entry := range p.entries {
		if entry.LabSlug == labSlug && entry.Status == "ready" {
			entry.Status = "allocated"
			p.allocMap[labSlug] = entry.ContainerID
			log.Printf("[pool] allocated pre-warmed container %s for lab %s", entry.ContainerID[:12], labSlug)
			return entry, nil
		}
	}

	// No pre-warmed container available — start a new one
	log.Printf("[pool] no pool container available for %s, starting new one", labSlug)
	return p.startNew(ctx, labSlug, image, port)
}

// Release returns a container to the pool or destroys it
func (p *ContainerPool) Release(ctx context.Context, labSlug string) {
	p.mu.Lock()
	containerID, ok := p.allocMap[labSlug]
	if !ok {
		p.mu.Unlock()
		return
	}
	delete(p.allocMap, labSlug)
	delete(p.entries, containerID)
	p.mu.Unlock()

	// Stop and remove the container
	go func() {
		timeout := 5
		p.manager.Stop(ctx, containerID, &timeout)
		p.manager.Remove(ctx, containerID)
		log.Printf("[pool] released and removed container %s", containerID[:12])
	}()
}

// Status returns the current pool status
func (p *ContainerPool) Status() map[string]any {
	p.mu.RLock()
	defer p.mu.RUnlock()

	ready := 0
	allocated := 0
	for _, entry := range p.entries {
		switch entry.Status {
		case "ready":
			ready++
		case "allocated":
			allocated++
		}
	}

	return map[string]any{
		"total":       len(p.entries),
		"ready":       ready,
		"allocated":   allocated,
		"target_size": p.poolSize,
	}
}

// Shutdown stops the pool and all containers
func (p *ContainerPool) Shutdown(ctx context.Context) {
	close(p.stopCh)

	p.mu.Lock()
	defer p.mu.Unlock()

	for id := range p.entries {
		timeout := 5
		p.manager.Stop(ctx, id, &timeout)
		p.manager.Remove(ctx, id)
	}

	log.Println("[pool] shutdown complete")
}

func (p *ContainerPool) startNew(ctx context.Context, labSlug string, image string, port int) (*PoolEntry, error) {
	// Find an available host port
	hostPort := p.findAvailablePort(port)

	cfg := ContainerConfig{
		Image: image,
		Ports: map[int]int{port: hostPort},
		Env: map[string]string{
			"MYSQL_HOST":     "localhost",
			"MYSQL_DATABASE": "cyberrange",
		},
		LabID:   labSlug,
		Timeout: p.timeout,
	}

	info, err := p.manager.CreateAndStart(ctx, cfg)
	if err != nil {
		return nil, fmt.Errorf("failed to start container for %s: %w", labSlug, err)
	}

	entry := &PoolEntry{
		ContainerID: info.ContainerID,
		LabSlug:     labSlug,
		Port:        hostPort,
		Status:      "allocated",
		CreatedAt:   time.Now(),
		ExpiresAt:   time.Now().Add(p.timeout),
	}

	p.entries[info.ContainerID] = entry
	p.allocMap[labSlug] = info.ContainerID

	return entry, nil
}

func (p *ContainerPool) warmUp(ctx context.Context) error {
	// Pre-warm is a no-op until we have actual Docker images built
	log.Println("[pool] warm-up: no images to pre-warm yet (build vulns first)")
	return nil
}

func (p *ContainerPool) maintenanceLoop(ctx context.Context) {
	ticker := time.NewTicker(30 * time.Second)
	defer ticker.Stop()

	for {
		select {
		case <-p.stopCh:
			return
		case <-ctx.Done():
			return
		case <-ticker.C:
			p.cleanup(ctx)
		}
	}
}

func (p *ContainerPool) cleanup(ctx context.Context) {
	p.mu.Lock()
	defer p.mu.Unlock()

	now := time.Now()
	for id, entry := range p.entries {
		if entry.Status == "ready" && now.After(entry.ExpiresAt) {
			go func(cid string) {
				timeout := 5
				p.manager.Stop(ctx, cid, &timeout)
				p.manager.Remove(ctx, cid)
			}(id)
			delete(p.entries, id)
			log.Printf("[pool] cleaned up expired container %s", id[:12])
		}
	}
}

func (p *ContainerPool) findAvailablePort(base int) int {
	used := make(map[int]bool)
	for _, entry := range p.entries {
		used[entry.Port] = true
	}

	port := base
	for used[port] {
		port++
	}
	return port
}
