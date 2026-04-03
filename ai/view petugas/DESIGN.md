# Design System Strategy: The Modern Sanctuary

## 1. Overview & Creative North Star
The North Star for this design system is **"The Digital Sanctuary."** 

In the context of a *Pesantren* (Islamic boarding school), a digital wallet must be more than a utility; it must be a space of trust, discipline, and calm. We move away from the cluttered, aggressive "fintech" aesthetic toward an editorial, high-end banking experience. This system breaks the "template" look by utilizing **intentional asymmetry** and **tonal depth**. We prioritize breathing room (white space) and a sophisticated layering of surfaces to create a sense of architectural stability. This is not just a wallet; it is a professional financial tool designed with the dignity of Islamic institutional values.

---

## 2. Colors & Surface Philosophy
Our palette is rooted in a prestigious `primary` (#004d4c) teal, supported by a sophisticated range of "cool greys" and "off-whites."

### The "No-Line" Rule
**Explicit Instruction:** Designers are prohibited from using 1px solid borders to section off content. Boundaries must be defined solely through background color shifts. 
*   *Example:* A section using `surface-container-low` (#f2f4f4) sitting directly on a `surface` (#f8fafa) background. 

### Surface Hierarchy & Nesting
Treat the UI as a series of physical layers—like stacked sheets of fine, heavy-weight paper.
*   **Base:** `surface` (#f8fafa) for the overall screen background.
*   **Mid-Level:** `surface-container-low` (#f2f4f4) for large grouping areas or page sections.
*   **Actionable Layer:** `surface-container-lowest` (#ffffff) for primary cards or interactive modules. This "pops" against the darker background without needing a shadow.

### The "Glass & Gradient" Rule
To elevate the experience, use **Glassmorphism** for floating elements (like bottom navigation bars or modal headers). Use `surface` colors at 80% opacity with a `backdrop-blur` of 20px. 
*   **Signature Textures:** For hero backgrounds (e.g., the balance card), use a subtle linear gradient from `primary` (#004d4c) to `primary-container` (#006766) at a 135-degree angle. This adds "soul" and depth that flat color cannot achieve.

---

## 3. Typography: Editorial Authority
We utilize a dual-font pairing to balance modern efficiency with institutional prestige.

*   **Display & Headlines (Manrope):** Chosen for its geometric yet warm character. Use `display-lg` (3.5rem) for balance amounts to give them a "monumental" feel.
*   **Body & Labels (Inter):** The workhorse for readability. Inter’s tall x-height ensures that even `body-sm` (0.75rem) transaction details remain legible under poor lighting conditions.
*   **Intentional Scale:** We use high-contrast sizing. A large `headline-lg` (2rem) title should sit near `body-md` (0.875rem) supporting text to create an "editorial" layout feel, rather than a uniform list.

---

## 4. Elevation & Depth: Tonal Layering
Traditional drop shadows are often a sign of "lazy" design. In this system, we achieve depth through **Tonal Layering**.

*   **The Layering Principle:** Stack `surface-container-lowest` cards on top of a `surface-container` background. The slight shift in hex value creates a soft, natural lift.
*   **Ambient Shadows:** If a "floating" effect is required (e.g., a Floating Action Button), the shadow must be extra-diffused. 
    *   *Spec:* Blur: 24px, Spread: -4px, Opacity: 6% of `on-surface` (#191c1d).
*   **The "Ghost Border" Fallback:** If a border is required for accessibility, use `outline-variant` (#bec9c8) at **15% opacity**. Never use a 100% opaque border.
*   **Glassmorphism:** Use semi-transparent `primary-container` fills for "Success" overlays to allow the underlying content to bleed through, softening the UI.

---

## 5. Components

### Buttons
*   **Primary:** Solid `primary` (#004d4c) with `on-primary` (#ffffff) text. Corner radius: `md` (0.75rem).
*   **Secondary:** `primary-fixed-dim` (#86d4d2) background with `on-primary-fixed-variant` (#00504f) text. No border.
*   **Tertiary:** Ghost style. No background, `primary` text. Use for low-priority actions like "View All."

### Input Fields
*   **Structure:** Forgo the "box" look. Use a `surface-container-high` (#e6e8e8) background with a radius of `md`.
*   **State:** On focus, do not change the border color. Instead, shift the background to `surface-container-highest` (#e1e3e3) and add a `primary` (#004d4c) "accent indicator" (a 2px vertical line on the left side of the input).

### Cards & Lists
*   **Constraint:** **Prohibit the use of divider lines.**
*   **Alternative:** Use vertical white space (Spacing `6` - 1.5rem) to separate list items. For transaction history, use alternating backgrounds (`surface` and `surface-container-low`) or simply group by date headers.

### Specialized Component: The "Zakat/Sadaqah" Quick-Action Chip
*   A custom rounded-pill component using `tertiary-container` (#cca73b) to distinguish charitable giving from standard commerce.

---

## 6. Do's and Don'ts

### Do:
*   **Do** use `xl` (1.5rem) rounded corners for top-level containers (like the main wallet dashboard) to make the app feel approachable.
*   **Do** use `primary-fixed` (#a2f0ee) as a background for "Incoming Fund" alerts to create a "fresh" and positive feeling.
*   **Do** align typography to a strict baseline grid to maintain "Institutional Order."

### Don't:
*   **Don't** use pure black (#000000) for text. Always use `on-surface` (#191c1d) to maintain a premium, softer contrast.
*   **Don't** use standard "Warning Yellow." Use the sophisticated `tertiary` (#755b00) gold/mustard tones for non-critical alerts.
*   **Don't** crowd the edges. Ensure a minimum of Spacing `5` (1.25rem) horizontal padding on all screens to maintain the "Sanctuary" feel.