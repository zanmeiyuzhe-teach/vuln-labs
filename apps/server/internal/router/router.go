package router

import (
	"fmt"
	"net/http"
	"time"

	"cyberrange-server/internal/handlers"
	"cyberrange-server/internal/middleware"

	"github.com/gin-gonic/gin"
	"github.com/jackc/pgx/v5/pgxpool"
	"github.com/redis/go-redis/v9"
)

func Setup(db *pgxpool.Pool, rdb *redis.Client, jwtSecret string) *gin.Engine {
	gin.SetMode(gin.ReleaseMode)
	r := gin.New()
	r.Use(gin.Recovery())
	r.Use(middleware.CORS())
	r.Use(middleware.Logger())

	// Health check
	r.GET("/health", func(c *gin.Context) {
		c.JSON(http.StatusOK, gin.H{"status": "ok", "time": time.Now().Format(time.RFC3339)})
	})

	// Handlers
	auth := handlers.NewAuthHandler(db, jwtSecret)
	categories := handlers.NewCategoryHandler(db)
	labs := handlers.NewLabHandler(db, rdb)
	lessons := handlers.NewLessonHandler(db)
	progress := handlers.NewProgressHandler(db)
	traffic := handlers.NewTrafficHandler(db)
	ai := handlers.NewAIHandler(db)

	v1 := r.Group("/api/v1")

	// Auth (public)
	authGroup := v1.Group("/auth")
	{
		authGroup.POST("/register", auth.Register)
		authGroup.POST("/login", auth.Login)
	}

	// Auth (protected)
	authProtected := v1.Group("/auth")
	authProtected.Use(middleware.JWTAuth(jwtSecret))
	{
		authProtected.GET("/me", auth.Me)
	}

	// Categories (public)
	catGroup := v1.Group("/categories")
	{
		catGroup.GET("", categories.List)
		catGroup.GET("/:slug", categories.GetBySlug)
	}

	// Labs (public list, protected actions)
	labGroup := v1.Group("/labs")
	{
		labGroup.GET("", labs.List)
		labGroup.GET("/:id", labs.Get)
	}

	labProtected := v1.Group("/labs")
	labProtected.Use(middleware.JWTAuth(jwtSecret))
	{
		labProtected.POST("/:id/start", labs.Start)
		labProtected.POST("/:id/stop", labs.Stop)
		labProtected.POST("/:id/reset", labs.Reset)
		labProtected.GET("/:id/status", labs.Status)
	}

	// Lessons (public)
	lessonGroup := v1.Group("/lessons")
	{
		lessonGroup.GET("", lessons.List)
		lessonGroup.GET("/:id", lessons.Get)
	}

	// Progress (protected)
	progGroup := v1.Group("/progress")
	progGroup.Use(middleware.JWTAuth(jwtSecret))
	{
		progGroup.GET("", progress.GetOverall)
		progGroup.GET("/:lab_id", progress.GetLab)
		progGroup.POST("/:lab_id", progress.Update)
	}

	// Traffic (protected)
	trafficGroup := v1.Group("/traffic")
	trafficGroup.Use(middleware.JWTAuth(jwtSecret))
	{
		trafficGroup.GET("/:lab_id", traffic.List)
		trafficGroup.POST("", traffic.Record)
		trafficGroup.DELETE("/:id", traffic.Delete)
	}

	// AI (protected)
	aiGroup := v1.Group("/ai")
	aiGroup.Use(middleware.JWTAuth(jwtSecret))
	{
		aiGroup.POST("/chat", ai.Chat)
		aiGroup.POST("/solve", ai.Solve)
		aiGroup.GET("/models", ai.GetModels)
		aiGroup.POST("/config", ai.SaveConfig)
	}

	_ = fmt.Sprintf("Routes registered")

	return r
}
