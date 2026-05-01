package providers

import (
	"context"
)

// ChatMessage represents a single message in a conversation
type ChatMessage struct {
	Role    string `json:"role"`
	Content string `json:"content"`
}

// ChatRequest is the input to any AI provider
type ChatRequest struct {
	Model    string        `json:"model"`
	Messages []ChatMessage `json:"messages"`
	Stream   bool          `json:"stream"`
	System   string        `json:"system,omitempty"`
}

// ChatChunk is a single chunk from a streaming response
type ChatChunk struct {
	Content      string `json:"content"`
	FinishReason string `json:"finish_reason,omitempty"`
	Error        string `json:"error,omitempty"`
}

// ChatResponse is the complete non-streaming response
type ChatResponse struct {
	Content      string `json:"content"`
	FinishReason string `json:"finish_reason,omitempty"`
	Usage        Usage  `json:"usage"`
}

// Usage tracks token consumption
type Usage struct {
	PromptTokens     int `json:"prompt_tokens"`
	CompletionTokens int `json:"completion_tokens"`
	TotalTokens      int `json:"total_tokens"`
}

// AIProvider defines the interface for all AI backends
type AIProvider interface {
	Name() string
	Chat(ctx context.Context, req ChatRequest) (*ChatResponse, error)
	StreamChat(ctx context.Context, req ChatRequest) (<-chan ChatChunk, error)
	ValidateAPIKey(ctx context.Context, apiKey string) error
	AvailableModels() []ModelInfo
}

// ModelInfo describes a model offered by a provider
type ModelInfo struct {
	ID       string `json:"id"`
	Name     string `json:"name"`
	Provider string `json:"provider"`
}
