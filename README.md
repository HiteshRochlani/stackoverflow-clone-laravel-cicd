# StackOverflow Clone with CI/CD Automation

A Laravel-based StackOverflow-style Q&A platform with automated CI/CD using Jenkins and Docker.

## Features
- User authentication and role-based access
- Question and answer workflow
- Unit testing with PHPUnit
- Automated build, test, and deployment using Jenkins

## Tech Stack
- PHP (Laravel)
- Jenkins (CI/CD)
- Docker & Docker Compose
- MySQL
- Git

## CI/CD Pipeline
- Jenkins pulls the repository on commit
- Builds the application
- Runs automated tests
- Deploys containers using Docker Compose

## Setup (Local)
1. git clone https://github.com/HiteshRochlani/stackoverflow-clone-laravel-cicd.git
2. composer install
3. cp .env.example .env
4. php artisan key:generate
5. docker-compose up --build
