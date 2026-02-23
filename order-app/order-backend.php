/**
 * ===================================================================
 * Agada Catering Order Form Handler
 * ===================================================================
 * This file contains all the server-side logic for the order form.
 * It defines configuration constants, creates the WordPress shortcode,
 * handles the AJAX submission, and generates the email notification.
 * ===================================================================
 */


/**
 * ===================================================================
 * Configuration
 * ===================================================================
 * All main settings are defined here as constants for easy management.
 * ===================================================================
 */
// The email address where new order notifications will be sent.
define('AGADA_EMAIL_TO', 'nd2020agada@gmail.com'); // !!! עדכן לכתובת המייל שלך !!!

// The subject line for the notification email.
define('AGADA_EMAIL_SUBJECT', '🎉 הזמנה חדשה מאגדה');

// The unique identifier for the WordPress Nonce (security token).
// This value is passed to the JavaScript side.
define('AGADA_NONCE_ACTION', 'agada_order_security_nonce');

// The unique name for the AJAX action.
// This is used to hook the PHP function into WordPress's AJAX system.
define('AGADA_AJAX_ACTION', 'submit_agada_order');


/**
 * ===================================================================
 * Shortcode: [agada_order_form]
 * ===================================================================
 * This shortcode generates the necessary JavaScript variables and then
 * embeds the main HTML form snippet.
 * ===================================================================
 */
add_shortcode('agada_order_form', function () {
    // IMPORTANT: Replace '3003' with the actual ID of your HTML/JS snippet in WPCode.
    $html_form_shortcode_id = '3003'; 

    // Create a JavaScript object with the AJAX URL and the security nonce.
    $ajax_obj_script  = '<script type="text/javascript">';
    $ajax_obj_script .= 'const agada_ajax_obj = ' . json_encode([
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce(AGADA_NONCE_ACTION),
    ]);
    $ajax_obj_script .= ';</script>';

    // Return the script followed by the rendered HTML form.
    return $ajax_obj_script . do_shortcode('[wpcode id="' . $html_form_shortcode_id . '"]');
});


/**
 * ===================================================================
 * AJAX Handler & Hooks
 * ===================================================================
 * This is the main function that runs when an order is submitted.
 * It's connected to WordPress via the hooks at the end.
 * ===================================================================
 */
function handle_agada_order_submission() {
    // 1. Security Verification: Checks if the nonce is valid.
    check_ajax_referer(AGADA_NONCE_ACTION, 'security');

    // 2. Data Parsing: Gets and decodes the JSON data from the form.
    $raw_data = isset($_POST['order_data']) ? wp_unslash($_POST['order_data']) : '';
    if (empty($raw_data)) {
        wp_send_json_error(['message' => 'Error: No order data received.']);
    }

    $order_data = json_decode($raw_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error(['message' => 'Error: Invalid JSON format.']);
    }

    // 3. Send Notifications: Calls helper functions to send emails/webhooks.
    $email_sent = send_agada_order_email($order_data);
    // send_agada_webhook($order_data); // Placeholder for future webhook integration.

    // 4. Send Response: Informs the client whether the submission was successful.
    if ($email_sent) {
        wp_send_json_success(['message' => 'Order sent successfully!']);
    } else {
        wp_send_json_error(['message' => 'Failed to send the order email.']);
    }
}
// Hooks the handler function into WordPress's AJAX system for both logged-in and guest users.
add_action('wp_ajax_nopriv_' . AGADA_AJAX_ACTION, 'handle_agada_order_submission');
add_action('wp_ajax_' . AGADA_AJAX_ACTION, 'handle_agada_order_submission');


/**
 * ===================================================================
 * Helper Functions
 * ===================================================================
 * These functions are responsible for specific tasks like sending emails.
 * ===================================================================
 */

/**
 * Sends the order confirmation email.
 */
function send_agada_order_email(array $data): bool {
    $customer_name = $data['customer']['name'] ?? 'לקוח חדש';
    $subject = AGADA_EMAIL_SUBJECT . ' - ' . $customer_name;
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    try {
        $html_body = generate_agada_email_html($data);
        return wp_mail(AGADA_EMAIL_TO, $subject, $html_body, $headers);
    } catch (\Exception $e) {
        error_log('Agada Email Template Error: ' . $e->getMessage());
        $fallback_body = "Failed to generate HTML email. Raw order data:\n\n" . wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return wp_mail(AGADA_EMAIL_TO, $subject, $fallback_body);
    }
}

/**
 * (Placeholder) Sends the order data to a webhook.
 */
function send_agada_webhook(array $data) {
    // Future logic for webhooks (e.g., to a CRM) will go here.
}

/**
 * Generates a well-formatted HTML email from the order data.
 */
function generate_agada_email_html(array $data): string {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="he" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <title>הזמנה חדשה - אגדה קייטרינג</title>
        <style>
            body { font-family: 'Arial', sans-serif; direction: rtl; text-align: right; background-color: #f9f9f9; margin: 0; padding: 15px; }
            .container { background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); max-width: 650px; margin: 0 auto; padding: 30px; border-top: 5px solid #56ab2f; }
            h1 { color: #56ab2f; font-size: 26px; margin-top: 0; }
            h2 { font-size: 20px; color: #333; margin-top: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; }
            .section { margin-bottom: 20px; }
            .section p { margin: 8px 0; line-height: 1.7; color: #555; }
            .section strong { color: #000; }
            ul { list-style: none; padding-right: 0; }
            li { margin-bottom: 8px; border-right: 3px solid #a8e063; padding-right: 12px; }
            .total-section { margin-top: 30px; padding-top: 20px; border-top: 2px solid #f0f0f0; text-align: left; }
            .total-value { font-size: 24px; font-weight: bold; color: #56ab2f; }
            pre.whatsapp-summary { white-space: pre-wrap; background-color: #f0f0f0; padding: 15px; border-radius: 5px; font-family: 'Arial', Courier, monospace; text-align: right; direction: rtl; border-left: 3px solid #25d366; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🎉 התקבלה הזמנה חדשה!</h1>

            <div class="section">
                <h2>👤 פרטי הלקוח</h2>
                <p><strong>שם מלא:</strong> <?php echo esc_html($data['customer']['name'] ?? 'לא הוזן'); ?></p>
                <p><strong>טלפון:</strong> <?php echo esc_html($data['customer']['phone'] ?? 'לא הוזן'); ?></p>
                <p><strong>תאריך אירוע:</strong> <?php echo esc_html($data['customer']['eventDate'] ?? 'לא הוזן'); ?></p>
				<p><strong>כתובת:</strong> <?php echo esc_html($data['customer']['address'] ?? 'לא הוזן'); ?></p>
            </div>

            <div class="section">
                <h2>📦 פרטי החבילה</h2>
                <p><strong>שם החבילה:</strong> <?php echo esc_html($data['summary']['packageName'] ?? 'לא הוזן'); ?></p>
                <p><strong>כמות מנות:</strong> <?php echo esc_html($data['summary']['peopleCount'] ?? 'לא הוזן'); ?></p>
            </div>

            <div class="section">
                <h2>📋 פירוט ההזמנה</h2>
                <?php
                $items_by_meal = [];
                $extras = [];
                if (!empty($data['items'])) {
                    foreach ($data['items'] as $item) {
                        if ($item['isExtra'] ?? false) {
                            $extras[] = $item;
                        } else {
                            $meal_name = $item['mealName'] ?? 'כללי';
                            $category_name = $item['categoryName'] ?? 'כללי';
                            $items_by_meal[$meal_name][$category_name][] = $item['itemName'];
                        }
                    }
                }

                foreach ($items_by_meal as $meal_name => $categories) {
                    echo '<h3>' . esc_html($meal_name) . '</h3>';
                    foreach ($categories as $category_name => $items) {
                        echo '<h4>' . esc_html($category_name) . ':</h4>';
                        echo '<ul>';
                        foreach ($items as $item_name) {
                            echo '<li>' . esc_html($item_name) . '</li>';
                        }
                        echo '</ul>';
                    }
                }
                ?>
            </div>
            
            <?php if (!empty($extras)): ?>
            <div class="section">
                <h2>✨ תוספות ופינוקים</h2>
                <ul>
                <?php foreach ($extras as $extra_item): ?>
                    <li><?php echo esc_html($extra_item['itemName']) . ' (כמות: ' . esc_html($extra_item['quantity']) . ')'; ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($data['customer']['notes'])): ?>
            <div class="section">
                <h2>📝 הערות</h2>
                <p><?php echo nl2br(esc_html($data['customer']['notes'])); ?></p>
            </div>
            <?php endif; ?>

            <?php if ($data['customer']['wantsQuote'] ?? false): ?>
            <div class="section">
                <p><strong>✓ הלקוח מעוניין בהצעת מחיר לניהול אירוע מלא.</strong></p>
            </div>
            <?php endif; ?>

            <div class="total-section">
                <span class="total-value">סה"כ: <?php echo number_format($data['summary']['totalPrice'] ?? 0, 2); ?> ₪</span>
            </div>
            
            <div class="section">
                <h2>📱 סיכום לשיתוף מהיר (WhatsApp)</h2>
                <pre class="whatsapp-summary"><?php echo esc_html($data['whatsappSummary'] ?? 'לא התקבל סיכום.'); ?></pre>
            </div>

        </div>
    </body>
    </html>
    <?php
    $html = ob_get_clean();
    if (!$html) {
        throw new \Exception('Email template rendering failed.');
    }
    return $html;
}

