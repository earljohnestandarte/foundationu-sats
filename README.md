# Foundation University - Student Affairs Ticketing System (SATS)

![CodeIgniter 4](https://img.shields.io/badge/CodeIgniter-4.x-EF4223?style=for-the-badge&logo=codeigniter)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.x-7952B3?style=for-the-badge&logo=bootstrap)
![jQuery](https://img.shields.io/badge/jQuery-3.x-0769AD?style=for-the-badge&logo=jquery)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql)

A custom, web-based helpdesk and ticketing system developed for **Foundation University (Dumaguete City) - Office of Student Life (OSL)**. SATS is designed to streamline how student concerns are routed, assigned, and resolved across various sub-offices (e.g., Counseling, Student Records, Wellness).

Inspired by modern SaaS platforms like Freshdesk, the system features a clean, spacious UI customized with Foundation University's maroon branding, providing a frictionless experience for both students and staff agents.

## Key Features

### For Students
* **Seamless Ticket Submission:** Easily submit concerns, select priority levels, and route them to the appropriate OSL sub-office.
* **Real-time Tracking:** View the real-time status of submitted tickets (Open, In Progress, Waiting on Student, Resolved).
* **Threaded Conversations:** Communicate directly with assigned agents through ticket replies.
* **Notifications:** Receive alerts when a ticket is assigned, updated, or replied to.

### For OSL Agents & Admins
* **Role-Based Workspaces:** Agents only see and manage tickets routed to their specific office.
* **Self-Assignment & Routing:** Agents can assign tickets to themselves or re-assign them to colleagues within the same office.
* **Assignment History Log:** Tracks the full history of ticket assignments and re-assignments.
* **Real-time Notifications:** In-app notification bell alerts agents to new tickets, replies, and status changes.

## Tech Stack

* **Backend Framework:** PHP / CodeIgniter 4 (Strict MVC Architecture)
* **Frontend:** Bootstrap 5, jQuery, custom CSS variables
* **Database:** MySQL / MariaDB
* **Architecture:** Lean controllers, fat models, utilizing CI4's built-in validation, route filters, and query builder.

## Database Schema Overview

The system runs on a highly normalized relational database:
1. `users` - Manages students, agents, and admins.
2. `offices` - OSL sub-departments.
3. `tickets` - Core ticket details (requester, resolver, status, priority).
4. `ticket_replies` - Messages attached to specific tickets.
5. `ticket_assignees` - Historical log of who assigned a ticket to whom.
6. `notifications` - Read/unread alerts for users.

## 🎨 UI/UX Philosophy
The frontend adheres strictly to a clean, minimalist SaaS design pattern:
* Generous padding and clean canvas backgrounds (`#f4f6f8`).
* Card-based layouts with subtle borders and shadows.
* Bootstrap rounded-pill badges for clear, color-coded ticket statuses.
* Themed with Foundation University's primary Maroon (`#800000`).

## 🚀 Installation & Setup

1. **Clone the repository**
   ```bash
   git clone [https://github.com/yourusername/foundationu-sats.git](https://github.com/yourusername/foundationu-sats.git)
   cd foundationu-sats

2. Install Dependencies
    Ensure you have Composer installed, then run:
    ```bash
    composer install

3. Environment Setup
    Copy the environment file and update your database credentials:
    ```bash 
    cp env .env

    Open .env, uncomment CI_ENVIRONMENT, set it to development, and configure your database.default settings.

4. Run Migrations & Seeders
    Create your tables using CI4's migration tool:
    ```bash
    php spark migrate

5. Start the Development Server
    ```bash
    php spark serve

## Developed for Foundation University, Dumaguete City.
