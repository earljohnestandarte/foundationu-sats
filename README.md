# Foundation University - Student Affairs Ticketing System (SATS)

![CodeIgniter 4](https://img.shields.io/badge/CodeIgniter-4.x-EF4223?style=for-the-badge&logo=codeigniter)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.x-7952B3?style=for-the-badge&logo=bootstrap)
![jQuery](https://img.shields.io/badge/jQuery-3.x-0769AD?style=for-the-badge&logo=jquery)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql)
![Quill](https://img.shields.io/badge/Quill.js-Rich%20Editor-52B0E7?style=for-the-badge)

A custom, web-based helpdesk and ticketing system developed for **Foundation University (Dumaguete City) - Office of Student Life (OSL)**. SATS streamlines how student concerns are submitted, tracked, escalated, and resolved across multiple departments (e.g., Counseling, Student Records, Wellness).

Inspired by modern SaaS platforms like Freshdesk, the system features a clean, spacious UI customized with Foundation University's maroon branding, providing a frictionless experience for students, agents, SAO staff, and administrators.

---

## Key Features

### For Students
- **Seamless Ticket Submission** — Submit concerns with priority levels, concern types, and department routing.
- **Real-time Tracking** — Monitor ticket status (Open, In Progress, Pending, Resolved, Closed, Archived).
- **Live Thread Updates** — New ticket replies appear in the conversation thread without a manual page refresh when the realtime server is running.
- **Threaded Conversations** — Communicate directly with assigned agents through ticket replies with a rich-text editor (Quill.js).
- **Escalation Requests** — Request escalation when a concern needs higher-level attention.
- **CSAT Feedback** — Rate resolved tickets to help measure service quality.
- **Notifications** — Receive alerts when a ticket is assigned, updated, replied to, or escalated.

### For OSL Agents
- **Role-Based Workspace** — Agents see and manage tickets routed to their assigned department.
- **Self-Assignment & Reassignment** — Assign tickets to yourself or transfer them to colleagues.
- **Assignment History Log** — Full audit trail of ticket assignments and reassignments.
- **Response Templates** — Use pre-written canned responses for common replies.
- **AI-Powered Suggestions** — Generate AI-assisted draft replies based on ticket context and agent role.
- **SLA Tracking** — Monitor response and resolution times against service-level agreements.
- **In-App Notifications** — Notification bell alerts agents to new tickets, replies, and status changes.
- **Realtime Ticket Conversations** — Agents receive live conversation thread updates for ticket replies while viewing a ticket.

### For SAO (Student Affairs Office)
- **Organization-Wide Dashboard** — View all escalated concerns across every department.
- **User Management** — Create, edit, and deactivate student and agent accounts.
- **Department Management** — View all departments (admin-only for delete).
- **Reports** — Date-filtered ticket reports with status breakdowns.
- **Response Templates** — Manage canned response templates used by agents.

### For Administrators
- **Full RBAC Control** — Manage all roles including admin, SAO, agent, and student accounts.
- **Department Administration** — Full CRUD over departments.
- **Elevated Ticket Access** — Access and manage any ticket across all departments.
- **System Oversight** — Complete visibility into all tickets, users, and system activity.

---

## Role Hierarchy

| Role    | Access Scope                                      |
|---------|---------------------------------------------------|
| Student | Own tickets only; submit, reply, escalate, rate   |
| Agent   | Tickets in assigned department(s)                 |
| SAO     | All tickets (elevated); manage students & agents  |
| Admin   | All tickets; manage all roles & departments       |

---

## Tech Stack

- **Backend Framework:** PHP / CodeIgniter 4 (Strict MVC Architecture)
- **Frontend:** Bootstrap 5, jQuery, Quill.js rich-text editor, custom CSS variables
- **Database:** MySQL / MariaDB
- **Realtime:** Ratchet WebSockets + ReactPHP HTTP server for live ticket thread updates
- **AI Integration:** OpenAI-compatible chat completions API (configurable provider)
- **Architecture:** Lean controllers, fat models, CI4 route filters, query builder, and native session handling

---

## Database Schema Overview

The system runs on a highly normalized relational database:

1. `users` — Students, agents, SAO staff, and admins with role-based access.
2. `departments` — OSL sub-departments (formerly offices).
3. `department_user` — Pivot table for agent ↔ department assignments.
4. `tickets` — Core ticket details with SLA timestamps, escalation flags, concern types, and priority.
5. `ticket_replies` — Threaded messages with reply-to chaining.
6. `ticket_assignees` — Historical assignment and reassignment audit log.
7. `ticket_feedback` — CSAT ratings and comments on resolved tickets.
8. `response_templates` — Canned response templates for agents.
9. `notifications` — Read/unread user alerts.

---

## Configuration

### AI Suggestions (`.env`)

Uses any OpenAI-compatible chat completions API. Configure these in your `.env` file:

```env
AI_API_KEY = sk-your-key-here
AI_BASE_URL = https://api.openai.com/v1
AI_MODEL = gpt-4o
```

See `.env.example` for all available settings.

### Realtime Replies (`.env`)

Live ticket thread updates use a separate realtime server process. Configure these in your `.env` file:

```env
realtime.websocketHost = 0.0.0.0
realtime.websocketPort = 8080
realtime.publishHost = 127.0.0.1
realtime.publishPort = 8081
realtime.secret = change-this-in-production
realtime.browserUrl = ws://127.0.0.1:8080
```

Notes:
- `realtime.secret` should be changed to a strong private value.
- `realtime.browserUrl` must match the URL the browser can actually reach, such as `ws://127.0.0.1:8080` for local HTTP development or `wss://your-domain:port` for HTTPS deployments.
- The internal publish endpoint is used by the app, not by browsers directly.

---

## UI/UX Philosophy

The frontend adheres strictly to a clean, minimalist SaaS design pattern:
- Generous padding and clean canvas backgrounds (`#f4f6f8`).
- Card-based layouts with subtle borders and shadows.
- Bootstrap rounded-pill badges for clear, color-coded ticket statuses.
- Themed with Foundation University's primary Maroon (`#800000`).
- Responsive sidebar navigation with active-state highlighting.

---

## Installation & Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/earljohnestandarte/foundationu-sats.git
   cd foundationu-sats
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   ```
   Open `.env` and configure your `database.default` settings and AI keys.

4. **Run Migrations & Seeders**
   ```bash
   php spark migrate
   php spark db:seed DatabaseSeeder
   ```

5. **Start the Development Server**
   ```bash
   php spark serve
   ```
   The application will be available at `http://localhost:8080`.

6. **Start the Realtime Server**
   ```bash
   php spark realtime:serve
   ```
   Keep this process running alongside `php spark serve` if you want live ticket reply updates in the conversation thread.

### Realtime Notes

- Live reply updates require both the main app server and the realtime server to be running.
- The browser should connect using the `realtime.browserUrl` value from `.env`, not the websocket bind address `0.0.0.0`.
- Opening the internal publish endpoint in a browser may return `{"success":false}` on a normal `GET` request. That is expected because the endpoint only accepts authenticated `POST` requests from the app.
- The ticket conversation thread updates in realtime. The notification bell can still remain non-realtime depending on the page and flow.

### Default Seed Accounts

All seed accounts use password: **`12345678`**

| Role    | Name           | Email                          |
|---------|----------------|--------------------------------|
| Admin   | Robert Smith   | robert.smith@foundationu.com   |
| Agent   | Mark Garcia    | mark.garcia@foundationu.com    |
| Student | Liza Santos    | liza.santos@foundationu.com    |

---

## Developed for Foundation University, Dumaguete City.
