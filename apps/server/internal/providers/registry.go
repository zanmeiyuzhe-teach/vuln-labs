package providers

import (
	"fmt"
	"sync"
)

// Registry manages available AI providers
type Registry struct {
	mu        sync.RWMutex
	providers map[string]AIProvider
}

// NewRegistry creates a provider registry with default providers
func NewRegistry() *Registry {
	r := &Registry{
		providers: make(map[string]AIProvider),
	}

	// Register default providers
	r.Register(NewOpenAIProvider(""))
	r.Register(NewAnthropicProvider(""))

	return r
}

// Register adds a provider to the registry
func (r *Registry) Register(p AIProvider) {
	r.mu.Lock()
	defer r.mu.Unlock()
	r.providers[p.Name()] = p
}

// Get retrieves a provider by name
func (r *Registry) Get(name string) (AIProvider, error) {
	r.mu.RLock()
	defer r.mu.RUnlock()

	p, ok := r.providers[name]
	if !ok {
		return nil, fmt.Errorf("provider %q not found", name)
	}
	return p, nil
}

// GetForModel returns the best provider for a given model ID
func (r *Registry) GetForModel(modelID string) (AIProvider, error) {
	r.mu.RLock()
	defer r.mu.RUnlock()

	for _, p := range r.providers {
		for _, m := range p.AvailableModels() {
			if m.ID == modelID {
				return p, nil
			}
		}
	}

	// Default to OpenAI-compatible for unknown models (DashScope, DeepSeek, etc.)
	p, ok := r.providers["openai-compatible"]
	if !ok {
		return nil, fmt.Errorf("no provider found for model %q", modelID)
	}
	return p, nil
}

// ListProviders returns all registered provider names
func (r *Registry) ListProviders() []string {
	r.mu.RLock()
	defer r.mu.RUnlock()

	names := make([]string, 0, len(r.providers))
	for name := range r.providers {
		names = append(names, name)
	}
	return names
}

// ListModels returns all available models across all providers
func (r *Registry) ListModels() []ModelInfo {
	r.mu.RLock()
	defer r.mu.RUnlock()

	var models []ModelInfo
	for _, p := range r.providers {
		models = append(models, p.AvailableModels()...)
	}
	return models
}
