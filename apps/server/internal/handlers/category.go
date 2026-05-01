package handlers

import (
	"net/http"

	"cyberrange-server/internal/models"

	"github.com/gin-gonic/gin"
	"github.com/jackc/pgx/v5/pgxpool"
)

type CategoryHandler struct {
	DB *pgxpool.Pool
}

func NewCategoryHandler(db *pgxpool.Pool) *CategoryHandler {
	return &CategoryHandler{DB: db}
}

func (h *CategoryHandler) List(c *gin.Context) {
	rows, err := h.DB.Query(c.Request.Context(),
		`SELECT id, name, slug, description, icon, color, sort_order FROM categories ORDER BY sort_order`)
	if err != nil {
		c.JSON(http.StatusInternalServerError, models.APIResponse{Success: false, Error: "failed to query categories"})
		return
	}
	defer rows.Close()

	var categories []models.Category
	for rows.Next() {
		var cat models.Category
		if err := rows.Scan(&cat.ID, &cat.Name, &cat.Slug, &cat.Desc, &cat.Icon, &cat.Color, &cat.SortOrder); err != nil {
			continue
		}
		categories = append(categories, cat)
	}

	c.JSON(http.StatusOK, models.APIResponse{Success: true, Data: categories})
}

func (h *CategoryHandler) GetBySlug(c *gin.Context) {
	slug := c.Param("slug")

	var cat models.Category
	err := h.DB.QueryRow(c.Request.Context(),
		`SELECT id, name, slug, description, icon, color, sort_order FROM categories WHERE slug = $1`, slug,
	).Scan(&cat.ID, &cat.Name, &cat.Slug, &cat.Desc, &cat.Icon, &cat.Color, &cat.SortOrder)

	if err != nil {
		c.JSON(http.StatusNotFound, models.APIResponse{Success: false, Error: "category not found"})
		return
	}

	c.JSON(http.StatusOK, models.APIResponse{Success: true, Data: cat})
}
