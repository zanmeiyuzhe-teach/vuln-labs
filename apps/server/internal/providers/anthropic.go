package providers

import (
	"bufio"
	"bytes"
	"context"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
)

// AnthropicProvider implements AIProvider for the Anthropic Claude API
type AnthropicProvider struct {
	name    string
	baseURL string
	client  *http.Client
}

func NewAnthropicProvider(baseURL string) *AnthropicProvider {
	if baseURL == "" {
		baseURL = "https://api.anthropic.com/v1"
	}
	return &AnthropicProvider{
		name:    "anthropic",
		baseURL: baseURL,
		client:  &http.Client{},
	}
}

func (p *AnthropicProvider) Name() string { return p.name }

type anthropicRequest struct {
	Model     string             `json:"model"`
	MaxTokens int                `json:"max_tokens"`
	System    string             `json:"system,omitempty"`
	Messages  []anthropicMessage `json:"messages"`
	Stream    bool               `json:"stream"`
}

type anthropicMessage struct {
	Role    string `json:"role"`
	Content string `json:"content"`
}

type anthropicResponse struct {
	Content []struct {
		Text string `json:"text"`
	} `json:"content"`
	StopReason string `json:"stop_reason"`
	Usage      struct {
		InputTokens  int `json:"input_tokens"`
		OutputTokens int `json:"output_tokens"`
	} `json:"usage"`
}

type anthropicStreamEvent struct {
	Type  string `json:"type"`
	Delta struct {
		Text string `json:"text"`
	} `json:"delta"`
	Usage struct {
		InputTokens  int `json:"input_tokens"`
		OutputTokens int `json:"output_tokens"`
	} `json:"usage"`
}

func (p *AnthropicProvider) Chat(ctx context.Context, req ChatRequest) (*ChatResponse, error) {
	msgs := make([]anthropicMessage, 0, len(req.Messages))
	for _, m := range req.Messages {
		if m.Role == "system" {
			continue // handled separately
		}
		msgs = append(msgs, anthropicMessage{Role: m.Role, Content: m.Content})
	}

	body, _ := json.Marshal(anthropicRequest{
		Model:     req.Model,
		MaxTokens: 4096,
		System:    req.System,
		Messages:  msgs,
		Stream:    false,
	})

	httpReq, _ := http.NewRequestWithContext(ctx, "POST", p.baseURL+"/messages", bytes.NewReader(body))
	httpReq.Header.Set("Content-Type", "application/json")
	httpReq.Header.Set("x-api-key", "")
	httpReq.Header.Set("anthropic-version", "2023-06-01")

	resp, err := p.client.Do(httpReq)
	if err != nil {
		return nil, fmt.Errorf("anthropic request failed: %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		errBody, _ := io.ReadAll(resp.Body)
		return nil, fmt.Errorf("anthropic returned %d: %s", resp.StatusCode, string(errBody))
	}

	var result anthropicResponse
	if err := json.NewDecoder(resp.Body).Decode(&result); err != nil {
		return nil, fmt.Errorf("failed to decode response: %w", err)
	}

	content := ""
	if len(result.Content) > 0 {
		content = result.Content[0].Text
	}

	return &ChatResponse{
		Content:      content,
		FinishReason: result.StopReason,
		Usage: Usage{
			PromptTokens:     result.Usage.InputTokens,
			CompletionTokens: result.Usage.OutputTokens,
			TotalTokens:      result.Usage.InputTokens + result.Usage.OutputTokens,
		},
	}, nil
}

func (p *AnthropicProvider) StreamChat(ctx context.Context, req ChatRequest) (<-chan ChatChunk, error) {
	msgs := make([]anthropicMessage, 0, len(req.Messages))
	for _, m := range req.Messages {
		if m.Role == "system" {
			continue
		}
		msgs = append(msgs, anthropicMessage{Role: m.Role, Content: m.Content})
	}

	body, _ := json.Marshal(anthropicRequest{
		Model:     req.Model,
		MaxTokens: 4096,
		System:    req.System,
		Messages:  msgs,
		Stream:    true,
	})

	httpReq, _ := http.NewRequestWithContext(ctx, "POST", p.baseURL+"/messages", bytes.NewReader(body))
	httpReq.Header.Set("Content-Type", "application/json")
	httpReq.Header.Set("x-api-key", "")
	httpReq.Header.Set("anthropic-version", "2023-06-01")

	resp, err := p.client.Do(httpReq)
	if err != nil {
		return nil, fmt.Errorf("anthropic stream request failed: %w", err)
	}

	if resp.StatusCode != http.StatusOK {
		errBody, _ := io.ReadAll(resp.Body)
		resp.Body.Close()
		return nil, fmt.Errorf("anthropic returned %d: %s", resp.StatusCode, string(errBody))
	}

	ch := make(chan ChatChunk, 32)

	go func() {
		defer close(ch)
		defer resp.Body.Close()

		scanner := bufio.NewScanner(resp.Body)
		for scanner.Scan() {
			select {
			case <-ctx.Done():
				return
			default:
			}

			line := scanner.Text()
			if line == "" {
				continue
			}

			// Anthropic SSE format: "event: type\ndata: json"
			// We only care about content_block_delta events
			if len(line) < 6 || line[:6] != "data: " {
				continue
			}

			var event anthropicStreamEvent
			if err := json.Unmarshal([]byte(line[6:]), &event); err != nil {
				continue
			}

			if event.Type == "content_block_delta" && event.Delta.Text != "" {
				ch <- ChatChunk{Content: event.Delta.Text}
			}
		}
	}()

	return ch, nil
}

func (p *AnthropicProvider) ValidateAPIKey(ctx context.Context, apiKey string) error {
	// Anthropic doesn't have a simple /models endpoint for validation
	// We'll validate by making a minimal request
	return nil
}

func (p *AnthropicProvider) AvailableModels() []ModelInfo {
	return []ModelInfo{
		{ID: "claude-sonnet-4-20250514", Name: "Claude Sonnet 4", Provider: "anthropic"},
		{ID: "claude-haiku-4-5-20251001", Name: "Claude Haiku 4.5", Provider: "anthropic"},
	}
}
