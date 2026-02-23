# Custom Knowledge: טופס הזמנה - קייטרינג אגדה

## Purpose

This is an online catering order form for "קייטרינג אגדה", allowing customers to compose a menu for events, select quantity, and submit order details digitally.

## Form Steps & Features

### Step 1: Menu Selection

- User is prompted to select from one of the main catering packages:
  - **אגדת הדגים**
    - 8 salads of choice
    - 2 starters including salmon filet
    - 3 hot side dishes
    - Handmade rolls & flatbreads
    - Price: ₪48/portion
  - **אגדת הבשרים**
    - 8 salads of choice
    - 3 main dishes (shoulder roast, asado, pargit)
    - 3 hot side dishes
    - Handmade rolls & flatbreads
    - Price: ₪53/portion
  - **האגדה המושלמת**
    - 8 salads of choice
    - 2 starters (salmon, meat roll)
    - 3 mains (shoulder roast, asado, pargit)
    - 3 hot side dishes
    - Handmade rolls & flatbreads
    - Price: ₪68/portion
  - **אגדת השבת**
    - 3 complete Shabbat meals
    - Fresh salads for every meal
    - Handmade rolls & flatbreads
    - Price: ₪140/portion

### Step 2: Quantity and Options

- User enters number of portions (min/max not specified; business may limit for logistics).

### Step 3: Extras & Services (Optional)

- Additional add-ons and services may be available (details should be updatable in system).

### Step 4: Contact & Event Details

- Required fields:
  - Full Name (שם מלא)
  - Phone (טלפון)
  - Address (כתובת)
  - Event Date (תאריך האירוע)
- Optional:
  - Notes (הערות)

### Step 5: Summary & Confirmation

- Form displays an order summary: package, quantity, price per portion, total price.
- Orders of 50+ שבת מנות: Bonus gift of handmade salmon fishballs in oriental sauce.
- On submission: Visual success message, option to send the order summary via WhatsApp.
- Message: “נציג יחזור אליכם בהקדם. ניתן לשלוח סיכום בוואטסאפ לתיעוד.”

## Functional & Design Requirements

- Fully RTL and Hebrew compliant.
- All logic for totals, validation, and success confirmation should be embedded in the assistant’s scope.
- Provide accessibility and seamless mobile/desktop experience.
- Strict validation of required fields.
- All static text must be easy to update for business owners (seasonal/package/price changes).

## Business Values

- Premium, Mehadrin kosher catering.
- Outstanding quality, generous portions, and customer-first approach.
- Fast and caring customer response after order.

## Voice & Style

- Friendly, confident, and supportive.
- Communicate bonus/gift succinctly when criteria is met.
- Always be up to date on menu or business changes.

## Brand Styleguide: קייטרינג אגדה

### Color Palette

- **Primary Gold**: #e1c084
  - Used for highlight elements, buttons, accents, and premium feel.
- **Deep Brown/Charcoal**: #2c2a1e
  - Dominant nav/text headings and footer, conveying warmth and luxury.
- **Ivory / Light Cream**: #f9f6ef
  - Background for most content areas for a clean, inviting base.
- **Rich Red**: #a05a2c
  - Used sparingly as highlight, alerts, or call-to-action.
- **Pure White**: #ffffff
  - Forms, cards, general backgrounds for creating contrast.

### Typography

- **Primary Font Family**: "Assistant", "Arial", "sans-serif"
  - Modern, friendly, highly readable Hebrew/Latin font.
- **Secondary Font Family**: "Varela Round", "sans-serif"
  - Used for body and accent text (especially in headings or callouts).
- **Font Sizes**:
  - Headings (h1): 2.2rem–2.5rem, bold, letterspacing 0.03em
  - Subheadings (h2/h3): 1.5rem–1.8rem
  - Regular Text: 1rem–1.1rem
  - Small/Meta: 0.85rem

### Motifs & Visual Identity

- **Rounded cards** and call-to-action buttons.
- Subtle drop-shadows for info cards and modals.
- Rich imagery: Food presented on white plates with abundant light, conveying freshness and hospitality.
- Elegant separators (thin gold/brown lines) between sections or menu packages.
- Fully **RTL support** across layouts, with Hebrew UI by default.
- Generous whitespace, avoiding clutter and focusing on food and service friendliness.

### UI Elements

- **Primary Buttons**: Filled gold background (#e1c084), dark brown or white text, large rounded borders.
- **Secondary Buttons**: Outlined or white, with gold border and brown text.
- **Form Fields**: White fill, brown text, gold focus border.
- **Success Messages**: Green check or warm gold highlight.
- **Icons**: Custom SVG/PNG in brand palette, minimalist.

### Brand Voice and Messaging

- Warm, authentic, professional, emphasizing “abundance,” “freshness,” “celebration,” and “peace of mind.”
- Copy is always positive and supportive, never formal or bureaucratic.
- “Above and beyond” tone for service and food quality.

---

_Colors and fonts verified as of September 2025; update if site branding changes._

---

_Last reviewed and synced: September 2025_
