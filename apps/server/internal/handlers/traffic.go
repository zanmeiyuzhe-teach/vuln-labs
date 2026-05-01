package handlers

import (
	"encoding/json"
	"net/http"

	"cyberrange-server/internal/models"

	"github.com/gin-gonic/gin"
	"github.com/google/uuid"
	"github.com/jackc/pgx/v5/pgxpool"
)

type TrafficHandler struct {
	DB *pgxpool.Pool
}

func NewTrafficHandler(db *pgxpool.Pool) *TrafficHandler {
	return &TrafficHandler{DB: db}
}

func (h *TrafficHandler) List(c *gin.Context) {
	userID := c.MustGet("user_id").(uuid.UUID)
	labID, err := uuid.Parse(c.Param("lab_id"))
	if err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{Success: false, Error: "invalid lab id"})
		return
	}

	rows, err := h.DB.Query(c.Request.Context(),
		`SELECT id, user_id, lab_id, session_id, step_number, request_method, request_url,
		 request_headers, request_body, response_status, response_headers, response_body, notes, created_at
		 FROM traffic_records WHERE user_id = $1 AND lab_id = $2 ORDER BY step_number, created_at`,
		userID, labID,
	)
	if err != nil {
		c.JSON(http.StatusInternalServerError, models.APIResponse{Success: false, Error: "failed to query traffic"})
		return
	}
	defer rows.Close()

	var records []models.TrafficRecord
	for rows.Next() {
		var r models.TrafficRecord
		if err := rows.Scan(&r.ID, &r.UserID, &r.LabID, &r.SessionID, &r.StepNumber,
			&r.RequestMethod, &r.RequestURL, &r.RequestHeaders, &r.RequestBody,
			&r.ResponseStatus, &r.ResponseHeaders, &r.ResponseBody, &r.Notes, &r.CreatedAt); err != nil {
			continue
		}
		records = append(records, r)
	}

	c.JSON(http.StatusOK, models.APIResponse{Success: true, Data: records})
}

func (h *TrafficHandler) Record(c *gin.Context) {
	userID := c.MustGet("user_id").(uuid.UUID)

	var req models.RecordTrafficRequest
	if err := c.ShouldBindJSON(&req); err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{Success: false, Error: err.Error()})
		return
	}

	reqHeaders, _ := json.Marshal(req.RequestHeaders)
	respHeaders, _ := json.Marshal(req.ResponseHeaders)

	var record models.TrafficRecord
	err := h.DB.QueryRow(c.Request.Context(),
		`INSERT INTO traffic_records (user_id, lab_id, session_id, step_number, request_method, request_url,
		 request_headers, request_body, response_status, response_headers, response_body, notes)
		 VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12)
		 RETURNING id, created_at`,
		userID, req.LabID, req.SessionID, req.StepNumber, req.RequestMethod, req.RequestURL,
		string(reqHeaders), req.RequestBody, req.ResponseStatus, string(respHeaders), req.ResponseBody, req.Notes,
	).Scan(&record.ID, &record.CreatedAt)

	if err != nil {
		c.JSON(http.StatusInternalServerError, models.APIResponse{Success: false, Error: "failed to record traffic"})
		return
	}

	c.JSON(http.StatusCreated, models.APIResponse{Success: true, Data: record})
}

func (h *TrafficHandler) Delete(c *gin.Context) {
	userID := c.MustGet("user_id").(uuid.UUID)
	id, err := uuid.Parse(c.Param("id"))
	if err != nil {
		c.JSON(http.StatusBadRequest, models.APIResponse{Success: false, Error: "invalid record id"})
		return
	}

	tag, err := h.DB.Exec(c.Request.Context(),
		`DELETE FROM traffic_records WHERE id = $1 AND user_id = $2`, id, userID)
	if err != nil || tag.RowsAffected() == 0 {
		c.JSON(http.StatusNotFound, models.APIResponse{Success: false, Error: "record not found"})
		return
	}

	c.JSON(http.StatusOK, models.APIResponse{Success: true, Message: "record deleted"})
}
