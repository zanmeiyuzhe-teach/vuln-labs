package handlers

import (
	"fmt"
	"net/http"
	"time"

	"cyberrange-server/internal/models"

	"github.com/gin-gonic/gin"
	"github.com/google/uuid"
	"github.com/jackc/pgx/v5/pgxpool"
	"github.com/redis/go-redis/v9"
)

type LabHandler struct {
	DB    *pgxpool.Pool
	Redis *redis.Client
}

func NewLabHandler(db *pgxpool.Pool, rdb *redis.Client) *LabHandler {
	return &LabHandler{DB: db, Redis: rdb}
}

func (h *LabHandler) List(c *gin.Context) {
	categorySlug := c.Query("category")
	difficulty := c.Query("difficulty")

	query := `SELECT l.id, l.category_id, l.name, l.slug, l.difficulty, l.description,
		l.learning_objectives, l.docker_image, l.default_port, l.timeout_minutes,
		l.knowledge_points, l.sort_order
		FROM labs l JOIN categories c ON l.category_id = c.id WHERE 1=1`

	args := []any{}
	argIdx := 1

	if categorySlug != "" {
		query += " AND c.slug = $" + itoa(argIdx)
		args = append(args, categorySlug)
		argIdx++
	}
	if difficulty != "" {
		query += " AND l.difficulty = $" + itoa(argIdx)
		args = append(args, difficulty)
		argIdx++
	}

	query += " ORDER BY l.sort_order"

	rows, err := h.DB.Query(c.Request.Context(), query, args...)
	if err != nil {
		c.JSON(http.StatusInternalServerError, models.APIResponse{Success: false, Error: "failed to query labs"})
		return
	}
	defer rows.Close()

	var labs []models.Lab
	for rows.Next() {
		var lab models.Lab
		if err := rows.Scan(&lab.ID, &lab.CategoryID, &lab.Name, &lab.Slug, &lab.Difficulty,
			&lab.Description, &lab.LearningObjectives, &lab.DockerImage,
			&lab.DefaultPort, &lab.TimeoutMinutes, &lab.KnowledgePoints, &lab.SortOrder); err != nil {
			continue
		}
		labs = append(labs, lab)
	}

	c.JSON(http.StatusOK, models.APIResponse{Success: true, Data: labs})
}

func (h *LabHandler) Get(c *gin.Context) {
	idStr := c.Param("id")
	id, err := uuid.Parse(idStr)
	if err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{Success: false, Error: "invalid lab id"})
		return
	}

	var lab models.Lab
	err = h.DB.QueryRow(c.Request.Context(),
		`SELECT id, category_id, name, slug, difficulty, description,
		learning_objectives, docker_image, default_port, timeout_minutes,
		knowledge_points, sort_order FROM labs WHERE id = $1`, id,
	).Scan(&lab.ID, &lab.CategoryID, &lab.Name, &lab.Slug, &lab.Difficulty,
		&lab.Description, &lab.LearningObjectives, &lab.DockerImage,
		&lab.DefaultPort, &lab.TimeoutMinutes, &lab.KnowledgePoints, &lab.SortOrder)

	if err != nil {
		c.JSON(http.StatusNotFound, models.APIResponse{Success: false, Error: "lab not found"})
		return
	}

	c.JSON(http.StatusOK, models.APIResponse{Success: true, Data: lab})
}

func (h *LabHandler) Start(c *gin.Context) {
	idStr := c.Param("id")
	id, err := uuid.Parse(idStr)
	if err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{Success: false, Error: "invalid lab id"})
		return
	}

	_ = id
	// TODO: integrate with container pool manager
	// For now, return a placeholder response
	c.JSON(http.StatusOK, models.APIResponse{
		Success: true,
		Data: models.ContainerStatus{
			ContainerID: "placeholder-" + idStr[:8],
			LabID:       idStr,
			URL:         "http://localhost:8081",
			Status:      "running",
			Uptime:      0,
			ExpiresAt:   time.Now().Add(time.Hour).Format(time.RFC3339),
		},
		Message: "lab started successfully",
	})
}

func (h *LabHandler) Stop(c *gin.Context) {
	c.JSON(http.StatusOK, models.APIResponse{Success: true, Message: "lab stopped"})
}

func (h *LabHandler) Reset(c *gin.Context) {
	c.JSON(http.StatusOK, models.APIResponse{Success: true, Message: "lab reset"})
}

func (h *LabHandler) Status(c *gin.Context) {
	c.JSON(http.StatusOK, models.APIResponse{
		Success: true,
		Data: models.ContainerStatus{
			Status: "not_running",
		},
	})
}

func itoa(n int) string {
	return fmt.Sprintf("%d", n)
}
