package handlers

import (
	"bufio"
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"net/http"

	"cyberrange-server/internal/models"
	"cyberrange-server/internal/providers"

	"github.com/gin-gonic/gin"
	"github.com/google/uuid"
	"github.com/jackc/pgx/v5/pgxpool"
)

type AIHandler struct {
	DB       *pgxpool.Pool
	Registry *providers.Registry
}

func NewAIHandler(db *pgxpool.Pool, registry *providers.Registry) *AIHandler {
	return &AIHandler{DB: db, Registry: registry}
}

func (h *AIHandler) Chat(c *gin.Context) {
	userID := c.MustGet("user_id").(uuid.UUID)

	var req models.AIChatRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{Success: false, Error: err.Error()})
		return
	}

	_ = userID

	// Load API key from header
	apiKey := c.GetHeader("X-API-Key")
	if apiKey == "" {
		c.JSON(http.StatusBadRequest, models.APIResponse{Success: false, Error: "missing API key, set X-API-Key header or configure in settings"})
		return
	}

	baseURL := c.GetHeader("X-API-Base-URL")
	if baseURL == "" {
		baseURL = "https://dashscope.aliyuncs.com/compatible-mode/v1"
	}

	model := req.Model
	if model == "" {
		model = "qwen3.5-plus"
	}

	// Build OpenAI-compatible request
	type chatMsg struct {
		Role    string `json:"role"`
		Content string `json:"content"`
	}
	type chatReq struct {
		Model    string    `json:"model"`
		Messages []chatMsg `json:"messages"`
		Stream   bool      `json:"stream"`
	}

	msgs := make([]chatMsg, len(req.Messages))
	for i, m := range req.Messages {
		msgs[i] = chatMsg{Role: m.Role, Content: m.Content}
	}

	body, _ := json.Marshal(chatReq{
		Model:    model,
		Messages: msgs,
		Stream:   true,
	})

	httpReq, _ := http.NewRequest("POST", baseURL+"/chat/completions", bytes.NewReader(body))
	httpReq.Header.Set("Content-Type", "application/json")
	httpReq.Header.Set("Authorization", "Bearer "+apiKey)

	resp, err := http.DefaultClient.Do(httpReq)
	if err != nil {
		c.JSON(http.StatusBadGateway, models.APIResponse{Success: false, Error: "failed to reach AI provider: " + err.Error()})
		return
	}
	defer resp.Body.Close()

	if resp.StatusCode != http.StatusOK {
		errBody, _ := io.ReadAll(resp.Body)
		c.JSON(http.StatusBadGateway, models.APIResponse{Success: false, Error: fmt.Sprintf("AI provider returned %d: %s", resp.StatusCode, string(errBody))})
		return
	}

	// Stream SSE response
	c.Header("Content-Type", "text/event-stream")
	c.Header("Cache-Control", "no-cache")
	c.Header("Connection", "keep-alive")

	scanner := bufio.NewScanner(resp.Body)
	for scanner.Scan() {
		line := scanner.Text()
		if line == "" {
			continue
		}
		fmt.Fprintf(c.Writer, "%s\n\n", line)
		c.Writer.Flush()
	}
}

func (h *AIHandler) Solve(c *gin.Context) {
	// Agent mode - similar to Chat but with attack-oriented system prompt
	h.Chat(c)
}

func (h *AIHandler) GetModels(c *gin.Context) {
	if h.Registry != nil {
		models := h.Registry.ListModels()
		c.JSON(http.StatusOK, models.APIResponse{Success: true, Data: models})
		return
	}

	// Fallback: hardcoded list
	models := []gin.H{
		{"id": "qwen3.5-plus", "name": "Qwen 3.5 Plus", "provider": "dashscope"},
		{"id": "kimi-k2.6", "name": "Kimi K2.6", "provider": "dashscope"},
		{"id": "claude-sonnet-4-20250514", "name": "Claude Sonnet 4", "provider": "anthropic"},
		{"id": "gpt-4o", "name": "GPT-4o", "provider": "openai"},
	}
	c.JSON(http.StatusOK, models.APIResponse{Success: true, Data: models})
}

func (h *AIHandler) SaveConfig(c *gin.Context) {
	var req models.AIConfigRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{Success: false, Error: err.Error()})
		return
	}
	userID := c.MustGet("user_id").(uuid.UUID)

	settings := map[string]any{
		"ai_provider": req.Provider,
		"ai_api_key":  req.APIKey,
		"ai_model":    req.Model,
		"ai_base_url": req.BaseURL,
	}

	settingsJSON, _ := json.Marshal(settings)

	_, err := h.DB.Exec(c.Request.Context(),
		`UPDATE users SET settings = settings || $1::jsonb, updated_at = NOW() WHERE id = $2`,
		string(settingsJSON), userID,
	)
	if err != nil {
		c.JSON(http.StatusInternalServerError, models.APIResponse{Success: false, Error: "failed to save config"})
		return
	}

	c.JSON(http.StatusOK, models.APIResponse{Success: true, Message: "AI config saved"})
}
