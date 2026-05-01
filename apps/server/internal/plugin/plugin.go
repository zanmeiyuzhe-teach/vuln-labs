package plugin

import "github.com/gin-gonic/gin"

// Plugin defines the interface every plugin must implement
type Plugin interface {
	// Name returns the unique plugin identifier
	Name() string

	// Version returns the plugin version string
	Version() string

	// Init initializes the plugin with configuration
	Init(config map[string]any) error

	// Start begins plugin operation
	Start() error

	// Stop gracefully shuts down the plugin
	Stop() error

	// HealthCheck returns nil if the plugin is healthy
	HealthCheck() error

	// RegisterRoutes adds plugin-specific API routes
	RegisterRoutes(router *gin.RouterGroup)
}

// Metadata holds plugin registration info
type Metadata struct {
	Name        string `json:"name"`
	Version     string `json:"version"`
	Description string `json:"description"`
	Author      string `json:"author"`
	Enabled     bool   `json:"enabled"`
}
