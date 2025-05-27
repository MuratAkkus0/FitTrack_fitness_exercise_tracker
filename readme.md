# Gym Training Tracker

## Project Description

A Symfony-based web application for gym members to track their workout programs and progress. Implements:

- **CRUD operations** with Doctrine ORM
- **MySQL database** integration
- **Basic Authentication** (user registration/login)
- **Workout logging** with exercise selection
- **Progress tracking** and social sharing via blog posts

## Key Features

sa

### User System

- Registration and login functionality
- Secure authentication controls

### Workout Management

- Create personalized training programs by selecting exercises
- Log and track workout sessions
- Edit/delete existing workout records
- Monitor training progress

### Social Sharing

- Share workout logs as blog posts

## Technical Stack

- **Backend**: Symfony 6, Doctrine ORM
- **Database**: MySQL
- **Frontend**: Twig templates, Tailwindcss
- **Containerization**: Docker, Docker Compose

## Quick Start

### Prerequisites

- Docker and Docker Compose installed

### Installation

1. Clone the repository
2. Start the application:

   ```bash
   make up
   # or
   docker-compose up -d
   ```

3. The application will be available at: http://localhost:8080

### Database

The database is automatically initialized with:

- All required tables
- Sample exercise data
- Symfony migrations are run automatically

### Useful Commands

```bash
# Start containers
make up

# Stop containers
make down

# Rebuild containers
make build

# View logs
make logs

# Clean everything (including database)
make clean

# Run migrations manually
make migrate

# Check migration status
make db-status

# Access PHP container shell
make shell-php

# Access MySQL shell
make shell-db
```

### Database Persistence

The database data is now persistent between container restarts thanks to Docker volumes. Your data will be preserved even when you run `docker-compose down` and `docker-compose up`.
