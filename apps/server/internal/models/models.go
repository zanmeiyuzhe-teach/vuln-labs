package models

import (
	"time"

	"github.com/google/uuid"
)

type User struct {
	ID           uuid.UUID `json:"id"`
	Username     string    `json:"username"`
	Email        string    `json:"email"`
	PasswordHash string    `json:"-"`
	Role         string    `json:"role"`
	AvatarURL    *string   `json:"avatar_url,omitempty"`
	Settings     any       `json:"settings,omitempty"`
	CreatedAt    time.Time `json:"created_at"`
	UpdatedAt    time.Time `json:"updated_at"`
}

type Category struct {
	ID        uuid.UUID `json:"id"`
	Name      string    `json:"name"`
	Slug      string    `json:"slug"`
	Desc      *string   `json:"description,omitempty"`
	Icon      *string   `json:"icon,omitempty"`
	Color     *string   `json:"color,omitempty"`
	SortOrder int       `json:"sort_order"`
}

type Lab struct {
	ID                 uuid.UUID `json:"id"`
	CategoryID         uuid.UUID `json:"category_id"`
	Name               string    `json:"name"`
	Slug               string    `json:"slug"`
	Difficulty         string    `json:"difficulty"`
	Description        *string   `json:"description,omitempty"`
	LearningObjectives []string  `json:"learning_objectives,omitempty"`
	DockerImage        *string   `json:"docker_image,omitempty"`
	DockerConfig       any       `json:"docker_config,omitempty"`
	DefaultPort        *int      `json:"default_port,omitempty"`
	TimeoutMinutes     int       `json:"timeout_minutes"`
	KnowledgePoints    []string  `json:"knowledge_points,omitempty"`
	SortOrder          int       `json:"sort_order"`
}

type Lesson struct {
	ID                 uuid.UUID `json:"id"`
	CategoryID         uuid.UUID `json:"category_id"`
	LabID              *uuid.UUID `json:"lab_id,omitempty"`
	Title              string    `json:"title"`
	ContentMD          string    `json:"content_md"`
	SortOrder          int       `json:"sort_order"`
	ReadingTimeMinutes int       `json:"reading_time_minutes"`
}

type UserProgress struct {
	ID              uuid.UUID  `json:"id"`
	UserID          uuid.UUID  `json:"user_id"`
	LabID           uuid.UUID  `json:"lab_id"`
	Status          string     `json:"status"`
	StartedAt       *time.Time `json:"started_at,omitempty"`
	CompletedAt     *time.Time `json:"completed_at,omitempty"`
	Attempts        int        `json:"attempts"`
	HintsUsed       int        `json:"hints_used"`
	AISolved        bool       `json:"ai_solved"`
	TimeSpentSeconds int       `json:"time_spent_seconds"`
}

type TrafficRecord struct {
	ID              uuid.UUID `json:"id"`
	UserID          uuid.UUID `json:"user_id"`
	LabID           uuid.UUID `json:"lab_id"`
	SessionID       *string   `json:"session_id,omitempty"`
	StepNumber      *int      `json:"step_number,omitempty"`
	RequestMethod   *string   `json:"request_method,omitempty"`
	RequestURL      *string   `json:"request_url,omitempty"`
	RequestHeaders  any       `json:"request_headers,omitempty"`
	RequestBody     *string   `json:"request_body,omitempty"`
	ResponseStatus  *int      `json:"response_status,omitempty"`
	ResponseHeaders any       `json:"response_headers,omitempty"`
	ResponseBody    *string   `json:"response_body,omitempty"`
	Notes           *string   `json:"notes,omitempty"`
	CreatedAt       time.Time `json:"created_at"`
}

type AISession struct {
	ID         uuid.UUID `json:"id"`
	UserID     uuid.UUID `json:"user_id"`
	LabID      uuid.UUID `json:"lab_id"`
	Mode       string    `json:"mode"`
	Model      *string   `json:"model,omitempty"`
	Messages   any       `json:"messages,omitempty"`
	TokensUsed int       `json:"tokens_used"`
	CreatedAt  time.Time `json:"created_at"`
}

// Request/Response DTOs

type RegisterRequest struct {
	Username string `json:"username" binding:"required,min=3,max=50"`
	Email    string `json:"email" binding:"required,email"`
	Password string `json:"password" binding:"required,min=6"`
}

type LoginRequest struct {
	Username string `json:"username" binding:"required"`
	Password string `json:"password" binding:"required"`
}

type LoginResponse struct {
	Token string `json:"token"`
	User  User   `json:"user"`
}

type UpdateProgressRequest struct {
	Status          *string `json:"status"`
	HintsUsed       *int    `json:"hints_used"`
	AISolved        *bool   `json:"ai_solved"`
	TimeSpentSeconds *int   `json:"time_spent_seconds"`
}

type RecordTrafficRequest struct {
	LabID          uuid.UUID    `json:"lab_id" binding:"required"`
	SessionID      *string      `json:"session_id"`
	StepNumber     *int         `json:"step_number"`
	RequestMethod  *string      `json:"request_method"`
	RequestURL     *string      `json:"request_url"`
	RequestHeaders map[string]string `json:"request_headers"`
	RequestBody    *string      `json:"request_body"`
	ResponseStatus *int         `json:"response_status"`
	ResponseHeaders map[string]string `json:"response_headers"`
	ResponseBody   *string      `json:"response_body"`
	Notes          *string      `json:"notes"`
}

type AIChatRequest struct {
	LabID    uuid.UUID `json:"lab_id" binding:"required"`
	Messages []Message `json:"messages" binding:"required"`
	Model    string    `json:"model"`
}

type Message struct {
	Role    string `json:"role"`
	Content string `json:"content"`
}

type AIConfigRequest struct {
	Provider string `json:"provider" binding:"required"`
	APIKey   string `json:"api_key" binding:"required"`
	Model    string `json:"model"`
	BaseURL  string `json:"base_url"`
}

type ContainerStatus struct {
	ContainerID string `json:"container_id"`
	LabID       string `json:"lab_id"`
	URL         string `json:"url"`
	Status      string `json:"status"`
	Uptime      int    `json:"uptime_seconds"`
	ExpiresAt   string `json:"expires_at"`
}

type APIResponse struct {
	Success bool   `json:"success"`
	Data    any    `json:"data,omitempty"`
	Error   string `json:"error,omitempty"`
	Message string `json:"message,omitempty"`
}
