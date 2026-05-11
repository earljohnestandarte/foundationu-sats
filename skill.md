# UI/UX Styling Guidelines: Foundation University SATS

## 1. Design Philosophy
The goal is to replicate the clean, modern, and spacious SaaS interface of Freshdesk, customized with Foundation University's (Dumaguete City) brand colors. The UI should prioritize readability, clear ticket status differentiation, and a minimalist structural layout.

## 2. Color Palette (Foundation University Theme)
We are using Bootstrap 5. These are the custom CSS variable overrides to apply:

* **Primary Brand (Maroon):** `#800000` (Use for navbars, primary buttons, active links, and brand accents)
* **Primary Hover:** `#5e0000` (Slightly darker maroon for button hover states)
* **Background (Canvas):** `#f4f6f8` (Freshdesk uses a very light grayish-blue background to make white cards pop)
* **Surface (Cards/Containers):** `#ffffff`
* **Text (Main):** `#12344d` (Dark slate, softer than pure black)
* **Text (Muted):** `#475867`
* **Border Color:** `#ebeff3`

## 3. Typography
* **Font Family:** Use a clean, modern Sans-Serif like `Inter`, `Roboto`, or standard system-ui.
* **Headings:** Bold and concise. Ticket subjects should be prominent.
* **Body Text:** 14px or 15px for ticket descriptions and replies to match SaaS density.

## 4. UI Component Rules (Bootstrap 5 Implementations)

### Layout & Spacing
* The overall background of the `<body>` must be `#f4f6f8`.
* Wrap main content areas in Bootstrap `.card` elements with a white background.

### Cards (The Freshdesk Feel)
* Do not use default Bootstrap heavy borders.
* **CSS Rule:** Cards should have `border: 1px solid #ebeff3 !important;`, `border-radius: 8px;`, and a very subtle shadow `box-shadow: 0 2px 4px rgba(18, 52, 77, 0.06);`.

### Buttons
* **Primary Action (e.g., "Submit Ticket", "Reply"):** `.btn-primary` overridden to use the Foundation Maroon (`#800000`) with no border.
* **Secondary Action (e.g., "Cancel", "Back"):** `.btn-outline-secondary` or subtle gray buttons to avoid clashing with the primary maroon.

### Tables (Ticket Lists)
* Use `.table .table-hover`.
* Remove vertical borders. Only use subtle horizontal bottom borders (`border-bottom: 1px solid #ebeff3;`).
* Rows should have a cursor pointer and a very light gray hover effect (`#f8f9fa`) to indicate clickability.
* Use generous padding in table cells (`padding: 12px 16px;`).

### Ticket Status Badges
Freshdesk relies heavily on color-coded badges to indicate status at a glance. Use Bootstrap `.badge` combined with these rounded pill styles (`.rounded-pill`):
* **Open:** `.bg-primary` (or a bright blue like `#2c5cc5` to stand out from the maroon brand)
* **In Progress:** `.bg-warning .text-dark` (Orange/Yellow)
* **Waiting on Student:** `.bg-info .text-dark` (Light Blue)
* **Resolved:** `.bg-success` (Green)
* **Closed:** `.bg-secondary` (Gray)

### Priority Indicators
* Low: Green text or subtle badge.
* Medium: Blue text.
* High: Orange text.
* Urgent: Red/Maroon text with a bold weight or icon (e.g., `<i class="fas fa-fire"></i>`).

## 5. Custom CSS Boilerplate
Whenever generating HTML/CSS, include or adhere to these core styles in the stylesheet:

```css
:root {
  --fu-maroon: #800000;
  --fu-maroon-hover: #5e0000;
  --fd-bg: #f4f6f8;
  --fd-border: #ebeff3;
  --fd-text-main: #12344d;
}

body {
  background-color: var(--fd-bg);
  color: var(--fd-text-main);
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

/* Custom Maroon Primary Button */
.btn-primary {
  background-color: var(--fu-maroon);
  border-color: var(--fu-maroon);
}
.btn-primary:hover {
  background-color: var(--fu-maroon-hover);
  border-color: var(--fu-maroon-hover);
}

/* Navbar Theme */
.navbar-brand-custom {
  background-color: var(--fu-maroon);
  color: #fff;
}

/* SaaS Card Style */
.card {
  border: 1px solid var(--fd-border);
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(18, 52, 77, 0.06);
}

/* Ticket Thread Bubbles */
.reply-bubble {
  padding: 1.5rem;
  border-bottom: 1px solid var(--fd-border);
}
.reply-bubble:last-child {
  border-bottom: none;
}