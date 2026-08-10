
# XIV Apparel - Form Specifications & Checkout Flows

This document details the step-by-step Checkout form fields, search inputs, and AJAX Cart controls as illustrated in the UI mockups.

---

## 1. Checkout Step-Form (Information Stage)

Matches the layout of `Group 88.jpg` (CHECKOUT -> INFORMATION -> SHIPPING -> PAYMENT).

```html
<div class="xiv-scope xiv-max-w-xl xiv-mx-auto">
    <h2 class="xiv-text-2xl xiv-font-black xiv-uppercase xiv-tracking-wide xiv-mb-6">CHECKOUT</h2>
  
    <!-- Tab Indicator -->
    <div class="xiv-flex xiv-gap-6 xiv-text-xs xiv-font-bold xiv-uppercase xiv-border-b xiv-border-xiv-gray-light xiv-pb-3 xiv-mb-6">
        <span class="xiv-text-xiv-black xiv-border-b-2 xiv-border-xiv-black xiv-pb-3">INFORMATION</span>
        <span class="xiv-text-xiv-gray-text">SHIPPING</span>
        <span class="xiv-text-xiv-gray-text">PAYMENT</span>
    </div>

    <form id="xiv-checkout-info-form" class="xiv-space-y-4">
        <?php wp_nonce_field('xiv_checkout_action', 'xiv_checkout_nonce'); ?>

        <!-- Contact Info -->
        <div class="xiv-space-y-3">
            <h4 class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-text-xiv-black">CONTACT INFO</h4>
            <input type="email" name="contact_email" placeholder="Email" required 
                   class="xiv-w-full xiv-p-3 xiv-text-sm xiv-bg-neutral-100/60 xiv-border xiv-border-xiv-gray-light focus:xiv-border-xiv-black focus:xiv-outline-none">
            <input type="tel" name="contact_phone" placeholder="Phone" required 
                   class="xiv-w-full xiv-p-3 xiv-text-sm xiv-bg-neutral-100/60 xiv-border xiv-border-xiv-gray-light focus:xiv-border-xiv-black focus:xiv-outline-none">
        </div>

        <!-- Shipping Address -->
        <div class="xiv-space-y-3 xiv-pt-4">
            <h4 class="xiv-text-xs xiv-font-bold xiv-uppercase xiv-text-xiv-black">SHIPPING ADDRESS</h4>
            <div class="xiv-grid xiv-grid-cols-2 xiv-gap-3">
                <input type="text" name="shipping_first_name" placeholder="First Name" required class="xiv-w-full xiv-p-3 xiv-text-sm xiv-bg-neutral-100/60 xiv-border xiv-border-xiv-gray-light focus:xiv-border-xiv-black">
                <input type="text" name="shipping_last_name" placeholder="Last Name" required class="xiv-w-full xiv-p-3 xiv-text-sm xiv-bg-neutral-100/60 xiv-border xiv-border-xiv-gray-light focus:xiv-border-xiv-black">
            </div>
          
            <select name="shipping_country" class="xiv-w-full xiv-p-3 xiv-text-sm xiv-bg-neutral-100/60 xiv-border xiv-border-xiv-gray-light focus:xiv-border-xiv-black">
                <option value="AU">Australia</option>
                <option value="ID">Indonesia</option>
                <option value="US">United States</option>
            </select>

            <input type="text" name="shipping_state" placeholder="State / Region" class="xiv-w-full xiv-p-3 xiv-text-sm xiv-bg-neutral-100/60 xiv-border xiv-border-xiv-gray-light">
            <input type="text" name="shipping_address" placeholder="Address" class="xiv-w-full xiv-p-3 xiv-text-sm xiv-bg-neutral-100/60 xiv-border xiv-border-xiv-gray-light">

            <div class="xiv-grid xiv-grid-cols-2 xiv-gap-3">
                <input type="text" name="shipping_city" placeholder="City" class="xiv-w-full xiv-p-3 xiv-text-sm xiv-bg-neutral-100/60 xiv-border xiv-border-xiv-gray-light">
                <input type="text" name="shipping_postal_code" placeholder="Postal Code" class="xiv-w-full xiv-p-3 xiv-text-sm xiv-bg-neutral-100/60 xiv-border xiv-border-xiv-gray-light">
            </div>
        </div>

        <button type="submit" class="xiv-w-full xiv-py-4 xiv-mt-6 xiv-bg-xiv-gray-light hover:xiv-bg-xiv-black hover:xiv-text-white xiv-font-bold xiv-text-sm xiv-flex xiv-justify-between xiv-items-center xiv-px-6 xiv-transition">
            <span>Shipping</span>
            <span>→</span>
        </button>
    </form>
</div>
```
