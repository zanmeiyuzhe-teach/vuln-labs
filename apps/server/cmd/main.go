package main

import (
	"fmt"
	"log"

	"cyberrange-server/internal/config"
	"cyberrange-server/internal/database"
	"cyberrange-server/internal/router"
)

func main() {
	cfg := config.Load()

	db, err := database.Connect(cfg)
	if err != nil {
		log.Fatalf("Failed to connect to database: %v", err)
	}
	defer db.Close()

	r := router.Setup(db.Pool, db.Redis, cfg.JWT.Secret)

	addr := fmt.Sprintf(":%s", cfg.Server.Port)
	log.Printf("CyberRange server starting on %s", addr)

	if err := r.Run(addr); err != nil {
		log.Fatalf("Failed to start server: %v", err)
	}
}
