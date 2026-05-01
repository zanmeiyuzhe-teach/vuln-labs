package handlers

import (
	"net/http"
	"time"

	"cyberrange-server/internal/models"

	"github.com/gin-gonic/gin"
	"github.com/google/uuid"
	"github.com/jackc/pgx/v5/pgxpool"
)

type ProgressHandler struct {
	DB *pgxpool.Pool
}

func NewProgressHandler(db *pgxpool.Pool) *ProgressHandler {
	return &ProgressHandler{DB: db}
}

func (h *ProgressHandler) GetOverall(c *gin.Context) {
	userID := c.MustGet("user_id").(uuid.UUID)

	var total, completed, inProgress int
	h.DB.QueryRow(c.Request.Context(),
		`SELECT COUNT(*) FROM labs`).Scan(&total)
	h.DB.QueryRow(c.Request.Context(),
		`SELECT COUNT(*) FROM user_progress WHERE user_id = $1 AND status = 'completed'`, userID).Scan(&completed)
	h.DB.QueryRow(c.Request.Context(),
		`SELECT COUNT(*) FROM user_progress WHERE user_id = $1 AND status = 'in_progress'`, userID).Scan(&inProgress)

	c.JSON(http.StatusOK, models.APIResponse{
		Success: true,
		Data: gin.H{
			"total":        total,
			"completed":    completed,
			"in_progress":  inProgress,
			"not_started":  total - completed - inProgress,
		},
	})
}

func (h *ProgressHandler) GetLab(c *gin.Context) {
	userID := c.MustGet("user_id").(uuid.UUID)
	labID, err := uuid.Parse(c.Param("lab_id"))
	if err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{Success: false, Error: "invalid lab id"})
		return
	}

	var p models.UserProgress
	err = h.DB.QueryRow(c.Request.Context(),
		`SELECT id, user_id, lab_id, status, started_at, completed_at, attempts, hints_used, ai_solved, time_spent_seconds
		 FROM user_progress WHERE user_id = $1 AND lab_id = $2`, userID, labID,
	).Scan(&p.ID, &p.UserID, &p.LabID, &p.Status, &p.StartedAt, &p.CompletedAt,
		&p.Attempts, &p.HintsUsed, &p.AISolved, &p.TimeSpentSeconds)

	if err != nil {
		c.JSON(http.StatusOK, models.APIResponse{
			Success: true,
			Data: models.UserProgress{
				Status: "not_started",
			},
		})
		return
	}

	c.JSON(http.StatusOK, models.APIResponse{Success: true, Data: p})
}

func (h *ProgressHandler) Update(c *gin.Context) {
	userID := c.MustGet("user_id").(uuid.UUID)
	labID, err := uuid.Parse(c.Param("lab_id"))
	if err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{Success: false, Error: "invalid lab id"})
		return
	}

	var req models.UpdateProgressRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{Success: false, Error: err.Error()})
		return
	}

	now := time.Now()

	_, err = h.DB.Exec(c.Request.Context(),
		`INSERT INTO user_progress (user_id, lab_id, status, started_at, attempts)
		 VALUES ($1, $2, 'in_progress', $3, 1)
		 ON CONFLICT (user_id, lab_id) DO UPDATE SET
		 status = COALESCE($4, user_progress.status),
		 completed_at = CASE WHEN $4 = 'completed' THEN $3 ELSE user_progress.completed_at END,
		 hints_used = COALESCE($5, user_progress.hints_used),
		 ai_solved = COALESCE($6, user_progress.ai_solved),
		 time_spent_seconds = COALESCE($7, user_progress.time_spent_seconds),
		 attempts = user_progress.attempts + 1`,
		userID, labID, now, req.Status, req.HintsUsed, req.AISolved, req.TimeSpentSeconds,
	)

	if err != nil {
		c.JSON(http.StatusInternalServerError, models.APIResponse{Success: false, Error: "failed to update progress"})
		return
	}

	c.JSON(http.StatusOK, models.APIResponse{Success: true, Message: "progress updated"})
}
