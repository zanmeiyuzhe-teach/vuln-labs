package plugin

import (
	"fmt"
	"sync"
)

// Registry manages plugin registration and lifecycle
type Registry struct {
	mu      sync.RWMutex
	plugins map[string]Plugin
	order   []string // registration order
}

// NewRegistry creates an empty plugin registry
func NewRegistry() *Registry {
	return &Registry{
		plugins: make(map[string]Plugin),
	}
}

// Register adds a plugin to the registry
func (r *Registry) Register(p Plugin) error {
	r.mu.Lock()
	defer r.mu.Unlock()

	name := p.Name()
	if _, exists := r.plugins[name]; exists {
		return fmt.Errorf("plugin %q already registered", name)
	}

	r.plugins[name] = p
	r.order = append(r.order, name)
	return nil
}

// Get retrieves a plugin by name
func (r *Registry) Get(name string) (Plugin, bool) {
	r.mu.RLock()
	defer r.mu.RUnlock()
	p, ok := r.plugins[name]
	return p, ok
}

// InitAll initializes all registered plugins with their configs
func (r *Registry) InitAll(configs map[string]map[string]any) error {
	r.mu.RLock()
	defer r.mu.RUnlock()

	for _, name := range r.order {
		p := r.plugins[name]
		cfg := configs[name]
		if cfg == nil {
			cfg = make(map[string]any)
		}
		if err := p.Init(cfg); err != nil {
			return fmt.Errorf("init plugin %q: %w", name, err)
		}
	}
	return nil
}

// StartAll starts all registered plugins in order
func (r *Registry) StartAll() error {
	r.mu.RLock()
	defer r.mu.RUnlock()

	for _, name := range r.order {
		if err := r.plugins[name].Start(); err != nil {
			return fmt.Errorf("start plugin %q: %w", name, err)
		}
	}
	return nil
}

// StopAll stops all plugins in reverse registration order
func (r *Registry) StopAll() error {
	r.mu.RLock()
	defer r.mu.RUnlock()

	var firstErr error
	for i := len(r.order) - 1; i >= 0; i-- {
		name := r.order[i]
		if err := r.plugins[name].Stop(); err != nil && firstErr == nil {
			firstErr = fmt.Errorf("stop plugin %q: %w", name, err)
		}
	}
	return firstErr
}

// HealthCheckAll checks the health of all plugins
func (r *Registry) HealthCheckAll() map[string]error {
	r.mu.RLock()
	defer r.mu.RUnlock()

	results := make(map[string]error)
	for _, name := range r.order {
		results[name] = r.plugins[name].HealthCheck()
	}
	return results
}

// List returns metadata for all registered plugins
func (r *Registry) List() []Metadata {
	r.mu.RLock()
	defer r.mu.RUnlock()

	var list []Metadata
	for _, name := range r.order {
		p := r.plugins[name]
		list = append(list, Metadata{
			Name:    p.Name(),
			Version: p.Version(),
		})
	}
	return list
}
