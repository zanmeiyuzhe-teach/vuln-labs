package plugin

import (
	"log"
	"time"

	"github.com/gin-gonic/gin"
)

// Host is the plugin host that manages the plugin lifecycle
type Host struct {
	registry *Registry
	stopCh   chan struct{}
}

// NewHost creates a new plugin host
func NewHost() *Host {
	return &Host{
		registry: NewRegistry(),
		stopCh:   make(chan struct{}),
	}
}

// Register adds a plugin to the host
func (h *Host) Register(p Plugin) error {
	return h.registry.Register(p)
}

// Boot initializes and starts all plugins
func (h *Host) Boot(configs map[string]map[string]any) error {
	if err := h.registry.InitAll(configs); err != nil {
		return err
	}
	if err := h.registry.StartAll(); err != nil {
		return err
	}

	// Background health check
	go h.healthCheckLoop()

	log.Println("[plugin-host] all plugins started")
	return nil
}

// Shutdown gracefully stops all plugins
func (h *Host) Shutdown() {
	close(h.stopCh)
	if err := h.registry.StopAll(); err != nil {
		log.Printf("[plugin-host] shutdown error: %v", err)
	}
	log.Println("[plugin-host] all plugins stopped")
}

// RegisterRoutes registers all plugin routes on the given router group
func (h *Host) RegisterRoutes(group *gin.RouterGroup) {
	plugins := h.registry.List()
	for _, meta := range plugins {
		p, _ := h.registry.Get(meta.Name)
		pluginGroup := group.Group("/" + meta.Name)
		p.RegisterRoutes(pluginGroup)
		log.Printf("[plugin-host] registered routes for %s", meta.Name)
	}
}

// Registry returns the plugin registry
func (h *Host) Registry() *Registry {
	return h.registry
}

func (h *Host) healthCheckLoop() {
	ticker := time.NewTicker(60 * time.Second)
	defer ticker.Stop()

	for {
		select {
		case <-h.stopCh:
			return
		case <-ticker.C:
			results := h.registry.HealthCheckAll()
			for name, err := range results {
				if err != nil {
					log.Printf("[plugin-host] health check failed for %s: %v", name, err)
				}
			}
		}
	}
}
