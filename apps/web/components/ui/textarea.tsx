import { forwardRef, type TextareaHTMLAttributes } from "react"
import { cn } from "@/lib/utils"

const Textarea = forwardRef<HTMLTextAreaElement, TextareaHTMLAttributes<HTMLTextAreaElement>>(
  ({ className, ...props }, ref) => {
    return (
      <textarea
        className={cn(
          "flex min-h-[80px] w-full rounded-lg border border-border bg-muted px-3 py-2 text-sm text-foreground",
          "placeholder:text-muted-foreground",
          "focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent",
          "disabled:cursor-not-allowed disabled:opacity-50",
          "transition-colors resize-none",
          className
        )}
        ref={ref}
        {...props}
      />
    )
  }
)
Textarea.displayName = "Textarea"

export { Textarea }
