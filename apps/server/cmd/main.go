package main

import (
	"context"
	"fmt"
	"log"
	"os"
	"os/signal"
	"syscall"
	"time"

	"cyberrange-server/internal/config"
	"cyberrange-server/internal/database"
	"cyberrange-server/internal/providers"
	"cyberrange-server/internal/router"
	"cyberrange-server/internal/services"
)

func main() {
	cfg := config.Load()

	// Database
	db, err := database.Connect(cfg)
	if err != nil {
		log.Fatalf("Failed to connect to database: %v", err)
	}
	defer db.Close()

	// AI Provider Registry
	registry := providers.NewRegistry()
	log.Printf("AI providers registered: %v", registry.ListProviders())

	// Container Pool (optional — gracefully degrades if Docker unavailable)
	var pool *services.ContainerPool
	cm, err := services.NewContainerManager()
	if err != nil {
		log.Printf("Warning: Docker not available, container pool disabled: %v", err)
	} else {
		pool = services.NewContainerPool(cm, 3, 60*time.Minute)
		if err := pool.Start(context.Background()); err != nil {
			log.Printf("Warning: container pool failed to start: %v", err)
			pool = nil
		} else {
			log.Println("Container pool started")
		}
	}

	// Router
	r := router.Setup(db.Pool, db.Redis, cfg.JWT.Secret, pool, registry)

	// Graceful shutdown
	go func() {
		sigCh := make(chan os.Signal, 1)
		signal.Notify(sigCh, syscall.SIGINT, syscall.SIGTERM)
		<-sigCh
		log.Println("Shutting down...")

		if pool != nil {
			pool.Shutdown(context.Background())
		}

		os.Exit(0)
	}()

	addr := fmt.Sprintf(":%s", cfg.Server.Port)
	log.Printf("CyberRange server starting on %s", addr)

	if err := r.Run(addr); err != nil {
		log.Fatalf("Failed to start server: %v", err)
	}
}
