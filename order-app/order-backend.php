/**
 * Shortcode: [order_form]  (unchanged)
 */
add_shortcode('order_form', function () {
    $ajax_obj_script  = '<script type="text/javascript">';
    $ajax_obj_script .= 'const catering_ajax_obj = ' . json_encode([
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('catering_order_nonce'),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $ajax_obj_script .= ';</script>';

    return $ajax_obj_script . do_shortcode('[wpcode id="2068"]');
});

/**
 * AJAX handler: decode + send enhanced HTML template, fallback to pretty JSON.
 */
function handle_catering_order_ajax() {
    check_ajax_referer('catering_order_nonce', 'security');

    $raw = isset($_POST['order_data']) ? wp_unslash($_POST['order_data']) : file_get_contents('php://input');
    if ($raw === '' || $raw === false) {
        wp_send_json_error(['message' => 'שגיאה: לא התקבלו נתוני הזמנה.']);
    }

    $data = json_decode($raw, true);
    $is_json = (json_last_error() === JSON_ERROR_NONE);

    $to       = 'nd2020agada@gmail.com'; // projects+agada@webistory.com, nd2020agada@gmail.com
    $name     = $is_json ? (string)($data['contact']['fullName'] ?? 'לקוח חדש') : 'לקוח חדש';
    $reply_to = $is_json ? sanitize_email($data['contact']['email'] ?? '') : '';
    $headersH = [
        'Content-Type: text/html; charset=UTF-8',
        'From: אתר קייטרינג אגדה <no-reply@' . parse_url(home_url(), PHP_URL_HOST) . '>',
        'Reply-To: ' . $name . ' <' . $reply_to . '>',
    ];
    $headersT = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: אתר קייטרינג אגדה <no-reply@' . parse_url(home_url(), PHP_URL_HOST) . '>',
        'Reply-To: ' . $name . ' <' . $reply_to . '>',
    ];

    $subject = 'הזמנה חדשה מהאתר - ' . $name;

    $sent = false;
    if ($is_json) {
        try {
            $html = generate_agada_catering_email_template($data);
            $sent = wp_mail($to, $subject, $html, $headersH);
        } catch (\Throwable $e) {
            // fall back below
        }
    }

    if (!$sent) {
        $pretty = $is_json ? wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : (string)$raw;
        $sent = wp_mail($to, $subject, "Raw order payload:\n\n".$pretty, $headersT);
    }

    $sent ? wp_send_json_success(['message' => 'ההזמנה נשלחה בהצלחה!'])
          : wp_send_json_error(['message' => 'שליחת המייל נכשלה.']);
}

/**
 * Enhanced Agada Catering Email Template - Modern Design
 */
function generate_agada_catering_email_template(array $data): string {
    $esc = static function ($v) { return esc_html((string)$v); };

    // Helper functions
    $format_currency = static function ($amount) {
        return '₪' . number_format((float)$amount, 0, '.', ',');
    };

    $format_date = static function ($date) {
        if (empty($date)) return '';
        $dt = DateTime::createFromFormat('Y-m-d', $date);
        return $dt ? $dt->format('d.m.Y') : $date;
    };

    // Package name mapping
    $package_names = [
        'shabbat_full' => 'אגדת השבת המלאה',
        'shabbat_basic' => 'אגדת השבת הבסיסית',
        'wedding' => 'חבילת חתונה',
        'event' => 'חבילת אירוע'
    ];

    // Extract data
    $packageId = $data['packageId'] ?? '';
    $packageName = $package_names[$packageId] ?? $packageId;
    $quantity = (int)($data['quantity'] ?? 0);
    $selections = $data['selections'] ?? [];
    $extraServices = $data['extraServices'] ?? [];
    $contact = $data['contact'] ?? [];
    $summary = $data['summary'] ?? '';
    $totalPrice = $data['totalPrice'] ?? 0;

    // Parse total from summary if not provided
    if (!$totalPrice && preg_match('/₪([\d,]+)/', $summary, $matches)) {
        $totalPrice = (int)str_replace(',', '', $matches[1]);
    }

    ob_start();
    ?>
<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>הזמנה חדשה - אגדה קייטרינג</title>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@300;400;500;600;700&family=Assistant:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0; background-color: #f8f9fa; font-family: 'Heebo', 'Assistant', Arial, sans-serif; direction: rtl; line-height: 1.6;">
    
    <!-- Main Container -->
    <div style="max-width: 800px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
        
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #80c047 0%, #6ba838 100%); padding: 30px 40px; text-align: center; color: white;">
            <h1 style="margin: 0; font-size: 28px; font-weight: 700; text-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                🍽️ הזמנה חדשה - אגדה קייטרינג
            </h1>
            <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">
                הזמנה התקבלה בהצלחה מהאתר
            </p>
        </div>

        <!-- Contact Information -->
        <div style="padding: 30px 40px; border-bottom: 3px solid #f0f0f0;">
            <h2 style="color: #80c047; font-size: 20px; font-weight: 600; margin: 0 0 20px 0; display: flex; align-items: center;">
                👤 פרטי הלקוח
            </h2>
            
            <div style="display: table; width: 100%; border-spacing: 0;">
                <?php if (!empty($contact['fullName'])): ?>
                <div style="display: table-row;">
                    <div style="display: table-cell; padding: 8px 15px 8px 0; font-weight: 600; color: #555; width: 120px;">שם מלא:</div>
                    <div style="display: table-cell; padding: 8px 0; color: #333;"><?php echo $esc($contact['fullName']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($contact['phone'])): ?>
                <div style="display: table-row;">
                    <div style="display: table-cell; padding: 8px 15px 8px 0; font-weight: 600; color: #555; width: 120px;">טלפון:</div>
                    <div style="display: table-cell; padding: 8px 0; color: #333; direction: ltr; text-align: right;">
                        <a href="tel:<?php echo $esc($contact['phone']); ?>" style="color: #80c047; text-decoration: none;">
                            <?php echo $esc($contact['phone']); ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($contact['address'])): ?>
                <div style="display: table-row;">
                    <div style="display: table-cell; padding: 8px 15px 8px 0; font-weight: 600; color: #555; width: 120px;">כתובת:</div>
                    <div style="display: table-cell; padding: 8px 0; color: #333;"><?php echo $esc($contact['address']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($contact['eventDate'])): ?>
                <div style="display: table-row;">
                    <div style="display: table-cell; padding: 8px 15px 8px 0; font-weight: 600; color: #555; width: 120px;">תאריך האירוע:</div>
                    <div style="display: table-cell; padding: 8px 0; color: #333; font-weight: 600;">
                        📅 <?php echo $esc($format_date($contact['eventDate'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($contact['notes'])): ?>
                <div style="display: table-row;">
                    <div style="display: table-cell; padding: 8px 15px 8px 0; font-weight: 600; color: #555; width: 120px; vertical-align: top;">הערות:</div>
                    <div style="display: table-cell; padding: 8px 0; color: #333; background: #f8f9fa; padding: 10px; border-radius: 6px; border-right: 3px solid #80c047;">
                        <?php echo nl2br($esc($contact['notes'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Summary -->
        <div style="padding: 30px 40px; border-bottom: 3px solid #f0f0f0;">
            <h2 style="color: #80c047; font-size: 20px; font-weight: 600; margin: 0 0 20px 0; display: flex; align-items: center;">
                📦 פרטי ההזמנה
            </h2>
            
            <div style="background: linear-gradient(45deg, #f8f9fa, #ffffff); border: 2px solid #e9ecef; border-radius: 12px; padding: 25px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <div style="flex: 1; min-width: 200px;">
                        <div style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 5px;">
                            <?php echo $esc($packageName); ?>
                        </div>
                        <div style="color: #666; font-size: 14px;">חבילה נבחרת</div>
                    </div>
                    <div style="text-align: center; min-width: 100px;">
                        <div style="font-size: 24px; font-weight: 700; color: #80c047;">
                            <?php echo $esc($quantity); ?>
                        </div>
                        <div style="color: #666; font-size: 14px;">מנות</div>
                    </div>
                    <?php if ($totalPrice > 0): ?>
                    <div style="text-align: left; min-width: 120px;">
                        <div style="font-size: 24px; font-weight: 700; color: #28a745;">
                            <?php echo $format_currency($totalPrice); ?>
                        </div>
                        <div style="color: #666; font-size: 14px;">סה"כ</div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Menu Selections -->
        <?php if (!empty($selections)): ?>
        <div style="padding: 30px 40px; border-bottom: 3px solid #f0f0f0;">
            <h2 style="color: #80c047; font-size: 20px; font-weight: 600; margin: 0 0 25px 0;">
                🍴 בחירות מהתפריט
            </h2>
            
            <?php foreach ($selections as $category => $items): ?>
                <?php if (!empty($items)): ?>
                <div style="margin-bottom: 25px; background: #ffffff; border: 1px solid #e9ecef; border-radius: 8px; overflow: hidden;">
                    <div style="background: #80c047; color: white; padding: 12px 20px; font-weight: 600; font-size: 16px;">
                        <?php 
                        $categoryDisplay = explode('_', $category);
                        echo $esc($categoryDisplay[0]); 
                        ?>
                    </div>
                    <div style="padding: 15px 20px;">
                        <?php if (is_array($items)): ?>
                            <ul style="margin: 0; padding: 0; list-style: none;">
                                <?php foreach ($items as $item): ?>
                                <li style="padding: 8px 0; border-bottom: 1px solid #f8f9fa; display: flex; align-items: center;">
                                    <span style="color: #80c047; margin-left: 10px; font-weight: bold;">•</span>
                                    <span style="color: #333; flex: 1;"><?php echo $esc($item); ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div style="color: #333; padding: 8px 0;">
                                <span style="color: #80c047; margin-left: 10px; font-weight: bold;">•</span>
                                <?php echo $esc($items); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Extra Services -->
        <?php if (!empty($extraServices)): ?>
        <div style="padding: 30px 40px; border-bottom: 3px solid #f0f0f0;">
            <h2 style="color: #80c047; font-size: 20px; font-weight: 600; margin: 0 0 25px 0;">
                ⭐ תוספות ושירותים
            </h2>
            
            <?php foreach ($extraServices as $serviceType => $services): ?>
                <?php if (!empty($services)): ?>
                <div style="margin-bottom: 20px; background: #f8f9fa; border-radius: 8px; padding: 20px; border-right: 4px solid #80c047;">
                    <h3 style="color: #555; font-size: 16px; font-weight: 600; margin: 0 0 15px 0;">
                        <?php 
                        $serviceLabels = [
                            'specialSides' => '🥗 תוספות מיוחדות',
                            'extraPlates' => '🍽️ מנות תוספת',
                            'desserts' => '🍰 קינוחים',
                            'eventServices' => '🎉 שירותי אירוע'
                        ];
                        echo $serviceLabels[$serviceType] ?? $esc($serviceType);
                        ?>
                    </h3>
                    
                    <?php if (is_array($services)): ?>
                        <?php if (isset($services[0]) && !is_array($services[0])): ?>
                            <!-- Simple array -->
                            <?php foreach ($services as $service): ?>
                            <div style="padding: 8px 0; color: #333; border-bottom: 1px dotted #ddd;">
                                <span style="color: #80c047; margin-left: 8px;">▪</span>
                                <?php echo $esc($service); ?>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Associative array with quantities -->
                            <?php foreach ($services as $service => $qty): ?>
                            <div style="padding: 8px 0; color: #333; border-bottom: 1px dotted #ddd; display: flex; justify-content: space-between; align-items: center;">
                                <span>
                                    <span style="color: #80c047; margin-left: 8px;">▪</span>
                                    <?php echo $esc($service); ?>
                                </span>
                                <span style="background: #80c047; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                    x<?php echo $esc($qty); ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <div style="padding: 8px 0; color: #333;">
                            <span style="color: #80c047; margin-left: 8px;">▪</span>
                            <?php echo $esc($services); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Summary Section -->
        <?php if (!empty($summary)): ?>
        <div style="padding: 30px 40px; border-bottom: 3px solid #f0f0f0;">
            <h2 style="color: #80c047; font-size: 20px; font-weight: 600; margin: 0 0 20px 0;">
                📄 סיכום ההזמנה
            </h2>
            <div style="background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 8px; padding: 20px; white-space: pre-wrap; font-family: 'Heebo', 'Assistant', Arial, sans-serif; line-height: 1.6; color: #333;">
                <?php echo $esc($summary); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Technical Details (Collapsed) -->
        <div style="padding: 30px 40px; background: #f8f9fa;">
            <details style="margin: 0;">
                <summary style="color: #666; font-size: 14px; cursor: pointer; padding: 10px 0; border-bottom: 1px solid #ddd;">
                    🔧 פרטים טכניים (לבדיקה)
                </summary>
                <div style="margin-top: 15px; background: #1a1a1a; color: #00ff00; padding: 15px; border-radius: 6px; font-family: 'Courier New', monospace; font-size: 12px; overflow-x: auto; direction: ltr; text-align: left;">
                    <pre style="margin: 0; white-space: pre-wrap; word-break: break-all;"><?php
                        echo esc_html(wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                    ?></pre>
                </div>
            </details>
        </div>

        <!-- Footer -->
        <div style="background: linear-gradient(135deg, #80c047 0%, #6ba838 100%); padding: 25px 40px; text-align: center; color: white;">
            <p style="margin: 0; font-size: 16px; font-weight: 500;">
                🌟 אגדה קייטרינג - האירוע שלכם, החלום שלנו
            </p>
            <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">
                הזמנה נשלחה אוטומטית מהאתר • <?php echo date('d.m.Y H:i'); ?>
            </p>
        </div>
        
    </div>
    
</body>
</html>
    <?php
    $html = ob_get_clean();
    
    if (!is_string($html) || trim($html) === '') {
        throw new \RuntimeException('Template rendering failed.');
    }
    
    return $html;
}

/**
 * Hooks (unchanged)
 */
add_action('wp_ajax_send_catering_order', 'handle_catering_order_ajax');
add_action('wp_ajax_nopriv_send_catering_order', 'handle_catering_order_ajax');