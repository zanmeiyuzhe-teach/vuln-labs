import { Badge } from "@/components/ui/badge"
import { difficultyConfig, type Difficulty } from "@/lib/utils"

interface DifficultyBadgeProps {
  difficulty: Difficulty
  className?: string
}

const variantMap: Record<Difficulty, "success" | "default" | "warning" | "destructive"> = {
  easy: "success",
  simple: "default",
  hard: "warning",
  hell: "destructive",
}

export function DifficultyBadge({ difficulty, className }: DifficultyBadgeProps) {
  const cfg = difficultyConfig[difficulty]
  return (
    <Badge variant={variantMap[difficulty]} className={className}>
      {cfg.label}
    </Badge>
  )
}
