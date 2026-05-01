package services

import (
	"context"
	"fmt"
	"io"
	"log"
	"time"

	"github.com/docker/docker/api/types"
	"github.com/docker/docker/api/types/container"
	"github.com/docker/docker/client"
	"github.com/docker/go-connections/nat"
)

// ContainerManager handles Docker container lifecycle
type ContainerManager struct {
	client *client.Client
}

// NewContainerManager creates a Docker container manager
func NewContainerManager() (*ContainerManager, error) {
	cli, err := client.NewClientWithOpts(client.FromEnv, client.WithAPIVersionNegotiation())
	if err != nil {
		return nil, fmt.Errorf("failed to create docker client: %w", err)
	}
	return &ContainerManager{client: cli}, nil
}

// ContainerConfig holds the configuration for starting a lab container
type ContainerConfig struct {
	Image      string
	Env        map[string]string
	Ports      map[int]int // container port -> host port
	Timeout    time.Duration
	LabID      string
}

// ContainerInfo holds information about a running container
type ContainerInfo struct {
	ContainerID string
	Names       []string
	Status      string
	Ports       []types.Port
	StartedAt   time.Time
}

// CreateAndStart creates and starts a new container
func (cm *ContainerManager) CreateAndStart(ctx context.Context, cfg ContainerConfig) (*ContainerInfo, error) {
	// Port bindings
	portBindings := nat.PortMap{}
	exposedPorts := nat.PortSet{}
	for containerPort, hostPort := range cfg.Ports {
		cp := nat.Port(fmt.Sprintf("%d/tcp", containerPort))
		exposedPorts[cp] = struct{}{}
		portBindings[cp] = []nat.PortBinding{
			{HostIP: "0.0.0.0", HostPort: fmt.Sprintf("%d", hostPort)},
		}
	}

	// Environment variables
	env := make([]string, 0, len(cfg.Env))
	for k, v := range cfg.Env {
		env = append(env, fmt.Sprintf("%s=%s", k, v))
	}

	// Labels for identification
	labels := map[string]string{
		"cyberrange":    "true",
		"cyberrange.lab": cfg.LabID,
	}

	// Container config
	containerCfg := &container.Config{
		Image:        cfg.Image,
		Env:          env,
		ExposedPorts: exposedPorts,
		Labels:       labels,
	}

	hostCfg := &container.HostConfig{
		PortBindings: portBindings,
		AutoRemove:   true,
		Resources: container.Resources{
			Memory:   256 * 1024 * 1024, // 256MB limit
			NanoCPUs: 500000000,          // 0.5 CPU
		},
	}

	// Create container
	containerName := fmt.Sprintf("cyberrange-%s-%d", cfg.LabID, time.Now().UnixNano())
	resp, err := cm.client.ContainerCreate(ctx, containerCfg, hostCfg, nil, nil, containerName)
	if err != nil {
		return nil, fmt.Errorf("failed to create container: %w", err)
	}

	// Start container
	if err := cm.client.ContainerStart(ctx, resp.ID, types.ContainerStartOptions{}); err != nil {
		return nil, fmt.Errorf("failed to start container %s: %w", resp.ID[:12], err)
	}

	log.Printf("[container] started %s for lab %s", resp.ID[:12], cfg.LabID)

	// Get container info
	info, err := cm.Inspect(ctx, resp.ID)
	if err != nil {
		return &ContainerInfo{ContainerID: resp.ID, Status: "started"}, nil
	}

	return info, nil
}

// Stop stops a running container
func (cm *ContainerManager) Stop(ctx context.Context, containerID string, timeout *int) error {
	if err := cm.client.ContainerStop(ctx, containerID, container.StopOptions{Timeout: timeout}); err != nil {
		return fmt.Errorf("failed to stop container %s: %w", containerID[:12], err)
	}
	log.Printf("[container] stopped %s", containerID[:12])
	return nil
}

// Remove removes a container
func (cm *ContainerManager) Remove(ctx context.Context, containerID string) error {
	if err := cm.client.ContainerRemove(ctx, containerID, types.ContainerRemoveOptions{Force: true}); err != nil {
		return fmt.Errorf("failed to remove container %s: %w", containerID[:12], err)
	}
	log.Printf("[container] removed %s", containerID[:12])
	return nil
}

// Inspect returns detailed info about a container
func (cm *ContainerManager) Inspect(ctx context.Context, containerID string) (*ContainerInfo, error) {
	resp, err := cm.client.ContainerInspect(ctx, containerID)
	if err != nil {
		return nil, fmt.Errorf("failed to inspect container %s: %w", containerID[:12], err)
	}

	info := &ContainerInfo{
		ContainerID: resp.ID,
		Status:      resp.State.Status,
	}

	if resp.State.StartedAt != "" {
		info.StartedAt, _ = time.Parse(time.RFC3339Nano, resp.State.StartedAt)
	}

	return info, nil
}

// Logs returns the logs of a container
func (cm *ContainerManager) Logs(ctx context.Context, containerID string, tail string) (string, error) {
	reader, err := cm.client.ContainerLogs(ctx, containerID, types.ContainerLogsOptions{
		ShowStdout: true,
		ShowStderr: true,
		Tail:       tail,
	})
	if err != nil {
		return "", fmt.Errorf("failed to get logs for %s: %w", containerID[:12], err)
	}
	defer reader.Close()

	data, err := io.ReadAll(reader)
	if err != nil {
		return "", err
	}

	return string(data), nil
}

// ListLabContainers lists all running CyberRange lab containers
func (cm *ContainerManager) ListLabContainers(ctx context.Context) ([]ContainerInfo, error) {
	containers, err := cm.client.ContainerList(ctx, types.ContainerListOptions{
		Filters: map[string][]string{
			"label": {"cyberrange=true"},
		},
	})
	if err != nil {
		return nil, fmt.Errorf("failed to list containers: %w", err)
	}

	var results []ContainerInfo
	for _, c := range containers {
		info := ContainerInfo{
			ContainerID: c.ID,
			Status:      c.Status,
			Ports:       c.Ports,
		}
		if !c.Created.IsZero() {
			info.StartedAt = time.Unix(c.Created, 0)
		}
		results = append(results, info)
	}

	return results, nil
}

// Close closes the Docker client connection
func (cm *ContainerManager) Close() error {
	return cm.client.Close()
}
