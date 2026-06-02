# DevTool AI (Booth 101) — Knowledge Base

## Booth Overview

DevTool AI is exhibiting at Booth 101 in the main expo hall. We are an AI-powered developer tools company founded in 2024, headquartered in San Francisco with offices in London and Bangalore. Our mission is to eliminate tedious developer workflows using artificial intelligence so engineers can focus on creative problem-solving.

## Products

### CodeWing — AI Code Review
CodeWing is our flagship product. It automatically reviews pull requests and provides:
- Summaries of what each PR changes and why
- Inline suggestions for bugs, performance issues, and style violations
- Test generation for uncovered code paths
- Security vulnerability detection using fine-tuned models trained on the OWASP Top 10
- Supports GitHub, GitLab, and Bitbucket integration
- Pricing: Free for public repos, $29/developer/month for private repos, Enterprise from $49/developer/month

CodeWing uses a combination of Claude Sonnet for reasoning-heavy review tasks and custom fine-tuned models for language-specific linting. It processes over 2 million PRs per month across 15,000 organizations.

### TestForge — AI Test Generation
TestForge generates unit, integration, and end-to-end tests from existing codebases. Key features:
- Analyzes your codebase structure and generates tests that match your existing patterns
- Supports PHP (PHPUnit, Pest), JavaScript (Jest, Vitest), Python (pytest), Go, and Rust
- Can generate tests from natural language descriptions: "Test that the checkout flow handles expired credit cards"
- Integrates into CI/CD pipelines with GitHub Actions, GitLab CI, and CircleCI
- Pricing: Included with CodeWing Enterprise; standalone at $19/developer/month

### BugSweep — AI Bug Detection
BugSweep scans production codebases for potential bugs before they reach users. It:
- Runs static analysis with AI-enhanced rule sets
- Identifies race conditions, memory leaks, and edge case handling issues
- Provides fix suggestions with before/after code comparisons
- Integrates with error monitoring tools (Sentry, Datadog, New Relic) to correlate detected patterns with real production errors
- Pricing: $39/developer/month

## Company Metrics

- Founded: March 2024
- Total funding: $55M (Seed $5M, Series A $10M, Series B $40M)
- Team size: 85 employees across engineering (45), sales (20), marketing (10), and operations (10)
- ARR: $8.2M as of Q1 2026
- Customers: 1,200+ organizations including Stripe, Notion, Linear, Vercel, and Figma
- NPS Score: 72
- GitHub stars on open-source components: 18,500+

## Leadership Team

Jennifer Okonkwo, CEO & Co-Founder — Previously Director of Engineering at Stripe where she led the Developer Productivity team. Stanford CS PhD dropout. Named to Forbes 30 Under 30 in 2025.

David Kim, CTO & Co-Founder — Previously Staff Engineer at GitHub where he worked on Copilot's code analysis engine. MIT CS graduate. Author of the open-source static analysis tool "AstWalker" with 5,000+ GitHub stars.

Priya Sharma, VP of Product — Previously PM Director at Atlassian, responsible for Bitbucket Pipelines. MBA from Wharton. Leading the product vision for CodeWing 2.0 launching Q3 2026.

## Tech Stack

DevTool AI's platform is built on:
- Backend: Python (ML services), Rust (performance-critical analysis engines), TypeScript (API layer)
- Infrastructure: Kubernetes on AWS, with GPU instances for ML inference
- Databases: PostgreSQL (relational data), pgvector (code embeddings), Redis (caching and job queues)
- ML Models: Fine-tuned CodeLlama and StarCoder variants for code analysis, Claude Sonnet via API for reasoning tasks
- Monitoring: Datadog, Sentry, custom telemetry pipeline

## What We're Launching at TechConf 2026

### CodeWing 2.0 Preview
We are giving exclusive previews of CodeWing 2.0 at Booth 101. New features include:
- Natural language code review requests: "Check if this PR introduces any N+1 queries"
- Real-time collaborative review rooms where multiple developers can discuss AI findings
- Custom rule builder with a visual interface — no YAML required
- Integration with Linear and Jira for automatic ticket creation from review findings
- SOC 2 Type II certified (new!)

### DevTool AI API
We are announcing our public API for the first time. Developers can now integrate CodeWing's analysis engine directly into their own tools and workflows. The API is REST-based with SDKs in TypeScript, Python, and Go. Launch pricing: $0.01 per 1,000 tokens processed.

## Booth Activities

Stop by Booth 101 for:
- Live demos of CodeWing 2.0 reviewing real-world open source PRs
- "Bug Hunt" challenge: Find bugs in our intentionally buggy codebase and win DevTool AI swag
- 1-on-1 meetings with our engineering team (book via the TechConf mobile app)
- Free CodeWing Enterprise trial for 3 months (normally $49/dev/month)
- Daily raffle: Win an iPad Pro at 4:00 PM each day
- We're hiring! Talk to our recruiting team about engineering, product, and sales roles

## Frequently Asked Questions

Q: Does CodeWing work with monorepos?
A: Yes, CodeWing has first-class monorepo support. It understands project boundaries and can be configured per-package. Our largest customer runs a monorepo with 500+ packages and 2,000+ developers.

Q: How do you handle code privacy?
A: For enterprise customers, we offer on-premise deployment and private cloud (AWS VPC, GCP VPC) options. All code is encrypted in transit and at rest. We never use customer code to train our models. SOC 2 Type II certified as of Q2 2026.

Q: What languages does TestForge support?
A: PHP (PHPUnit, Pest), JavaScript/TypeScript (Jest, Vitest), Python (pytest), Go (testing package), and Rust (cargo test). Ruby (RSpec) and Java (JUnit) support is coming Q4 2026.

Q: Can I try DevTool AI for free?
A: Yes! CodeWing is free forever for public repositories. For private repos, we offer a 14-day free trial with no credit card required. Visit booth 101 for an extended 3-month trial.

Q: How long does a CodeWing review take?
A: Most PRs are reviewed in under 2 minutes. Large PRs (500+ lines changed) may take up to 5 minutes. We process reviews asynchronously so developers aren't blocked.

## Competitive Differentiators

Compared to competitors like CodeRabbit, CodeClimate, and SonarQube:
- DevTool AI uses multi-model AI (not just static analysis rules) for deeper semantic understanding
- We support 5+ languages with specialized models per language
- Our TestForge product generates actual running tests, not just suggestions
- Enterprise SLA: 99.9% uptime with 24/7 support
- Open-source foundation: Core analysis engines are open source on GitHub
