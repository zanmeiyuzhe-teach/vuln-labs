package handlers

import (
	"net/http"

	"cyberrange-server/internal/models"

	"github.com/gin-gonic/gin"
	"github.com/google/uuid"
	"github.com/jackc/pgx/v5/pgxpool"
)

type LessonHandler struct {
	DB *pgxpool.Pool
}

func NewLessonHandler(db *pgxpool.Pool) *LessonHandler {
	return &LessonHandler{DB: db}
}

func (h *LessonHandler) List(c *gin.Context) {
	categorySlug := c.Query("category")

	query := `SELECT l.id, l.category_id, l.lab_id, l.title, l.content_md, l.sort_order, l.reading_time_minutes
		FROM lessons l JOIN categories c ON l.category_id = c.id`

	args := []any{}
	if categorySlug != "" {
		query += " WHERE c.slug = $1"
		args = append(args, categorySlug)
	}
	query += " ORDER BY l.sort_order"

	rows, err := h.DB.Query(c.Request.Context(), query, args...)
	if err != nil {
		c.JSON(http.StatusInternalServerError, models.APIResponse{Success: false, Error: "failed to query lessons"})
		return
	}
	defer rows.Close()

	var lessons []models.Lesson
	for rows.Next() {
		var l models.Lesson
		if err := rows.Scan(&l.ID, &l.CategoryID, &l.LabID, &l.Title, &l.ContentMD, &l.SortOrder, &l.ReadingTimeMinutes); err != nil {
			continue
		}
		lessons = append(lessons, l)
	}

	c.JSON(http.StatusOK, models.APIResponse{Success: true, Data: lessons})
}

func (h *LessonHandler) Get(c *gin.Context) {
	idStr := c.Param("id")
	id, err := uuid.Parse(idStr)
	if err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{Success: false, Error: "invalid lesson id"})
		return
	}

	var l models.Lesson
	err = h.DB.QueryRow(c.Request.Context(),
		`SELECT id, category_id, lab_id, title, content_md, sort_order, reading_time_minutes FROM lessons WHERE id = $1`, id,
	).Scan(&l.ID, &l.CategoryID, &l.LabID, &l.Title, &l.ContentMD, &l.SortOrder, &l.ReadingTimeMinutes)

	if err != nil {
		c.JSON(http.StatusNotFound, models.APIResponse{Success: false, Error: "lesson not found"})
		return
	}

	c.JSON(http.StatusOK, models.APIResponse{Success: true, Data: l})
}
