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

// OpenAIProvider implements AIProvider for OpenAI-compatible APIs
// This covers: OpenAI, DashScope (Alibaba), DeepSeek, and any OpenAI-compatible endpoint
type OpenAIProvider struct {
	name    string
	baseURL string
	client  *http.Client
}

func NewOpenAIProvider(baseURL string) *OpenAIProvider {
	if baseURL == "" {
		baseURL = "https://api.openai.com/v1"
	}
	return &OpenAIProvider{
		name:    "openai-compatible",
		baseURL: baseURL,
		client:  &http.Client{},
	}
}

func (p *OpenAIProvider) Name() string { return p.name }

type openaiMessage struct {
	Role    string `json:"role"`
	Content string `json:"content"`
}

type openaiRequest struct {
	Model    string          `json:"model"`
	Messages []openaiMessage `json:"messages"`
	Stream   bool            `json:"stream"`
}

type openaiResponse struct {
	Choices []struct {
		Message struct {
			Content string `json:"content"`
		} `json:"message"`
		FinishReason string `json:"finish_reason"`
	} `json:"choices"`
	Usage struct {
		PromptTokens     int `json:"prompt_tokens"`
		CompletionTokens int `json:"completion_tokens"`
		TotalTokens      int `json:"total_tokens"`
	} `json:"usage"`
}

type openaiStreamChunk struct {
	Choices []struct {
		Delta struct {
			Content string `json:"content"`
		} `json:"delta"`
		FinishReason string `json:"finish_reason"`
	} `json:"choices"`
}

func (p *OpenAIProvider) Chat(ctx context.Context, req ChatRequest) (*ChatResponse, error) {
	msgs := make([]openaiMessage, len(req.Messages))
	for i, m := range req.Messages {
		msgs[i] = openaiMessage{Role: m.Role, Content: m.Content}
	}

	body, _ := json.Marshal(openaiRequest{
		Model:    req.Model,
		Messages: msgs,
		Stream:   false,
	})

	httpReq, _ := http.NewRequestWithContext(ctx, "POST", p.baseURL+"/chat/completions", bytes.NewReader(body))
	httpReq.Header.Set("Content-Type", "application/json")

	resp, err := p.client.Do(httpReq)
	if err != nil {
		return nil, fmt.Errorf("openai request failed: %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		errBody, _ := io.ReadAll(resp.Body)
		return nil, fmt.Errorf("openai returned %d: %s", resp.StatusCode, string(errBody))
	}

	var result openaiResponse
	if err := json.NewDecoder(resp.Body).Decode(&result); err != nil {
		return nil, fmt.Errorf("failed to decode response: %w", err)
	}

	if len(result.Choices) == 0 {
		return nil, fmt.Errorf("no choices in response")
	}

	return &ChatResponse{
		Content:      result.Choices[0].Message.Content,
		FinishReason: result.Choices[0].FinishReason,
		Usage: Usage{
			PromptTokens:     result.Usage.PromptTokens,
			CompletionTokens: result.Usage.CompletionTokens,
			TotalTokens:      result.Usage.TotalTokens,
		},
	}, nil
}

func (p *OpenAIProvider) StreamChat(ctx context.Context, req ChatRequest) (<-chan ChatChunk, error) {
	msgs := make([]openaiMessage, len(req.Messages))
	for i, m := range req.Messages {
		msgs[i] = openaiMessage{Role: m.Role, Content: m.Content}
	}

	body, _ := json.Marshal(openaiRequest{
		Model:    req.Model,
		Messages: msgs,
		Stream:   true,
	})

	httpReq, _ := http.NewRequestWithContext(ctx, "POST", p.baseURL+"/chat/completions", bytes.NewReader(body))
	httpReq.Header.Set("Content-Type", "application/json")

	resp, err := p.client.Do(httpReq)
	if err != nil {
		return nil, fmt.Errorf("openai stream request failed: %w", err)
	}

	if resp.StatusCode != http.StatusOK {
		errBody, _ := io.ReadAll(resp.Body)
		resp.Body.Close()
		return nil, fmt.Errorf("openai returned %d: %s", resp.StatusCode, string(errBody))
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
			if line == "" || line == "data: [DONE]" {
				if line == "data: [DONE]" {
					return
				}
				continue
			}

			if len(line) < 6 || line[:6] != "data: " {
				continue
			}

			var chunk openaiStreamChunk
			if err := json.Unmarshal([]byte(line[6:]), &chunk); err != nil {
				continue
			}

			if len(chunk.Choices) > 0 {
				ch <- ChatChunk{
					Content:      chunk.Choices[0].Delta.Content,
					FinishReason: chunk.Choices[0].FinishReason,
				}
			}
		}
	}()

	return ch, nil
}

func (p *OpenAIProvider) ValidateAPIKey(ctx context.Context, apiKey string) error {
	// A lightweight check — list models
	httpReq, _ := http.NewRequestWithContext(ctx, "GET", p.baseURL+"/models", nil)
	httpReq.Header.Set("Authorization", "Bearer "+apiKey)

	resp, err := p.client.Do(httpReq)
	if err != nil {
		return fmt.Errorf("failed to validate API key: %w", err)
	}
	defer resp.Body.Close()

	if resp.StatusCode == http.StatusUnauthorized {
		return fmt.Errorf("invalid API key")
	}
	if resp.StatusCode != http.StatusOK {
		return fmt.Errorf("unexpected status: %d", resp.StatusCode)
	}
	return nil
}

func (p *OpenAIProvider) AvailableModels() []ModelInfo {
	return []ModelInfo{
		{ID: "gpt-4o", Name: "GPT-4o", Provider: "openai"},
		{ID: "gpt-4o-mini", Name: "GPT-4o Mini", Provider: "openai"},
		{ID: "gpt-4-turbo", Name: "GPT-4 Turbo", Provider: "openai"},
	}
}
