<?php
/**
 * Settings Admin Template
 *
 * @package ClubCore
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
$tabs = [
    'general'       => __( 'عمومی', 'clubcore' ),
    'access'        => __( 'دسترسی‌ها', 'clubcore' ),
    'sms'           => __( 'پیامک', 'clubcore' ),
    'pattern'       => __( 'الگوها', 'clubcore' ),
    'woocommerce'   => __( 'ووکامرس', 'clubcore' ),
    'appearance'    => __( 'ظاهر', 'clubcore' ),
    'import_export' => __( 'درون‌ریزی / برون‌بری', 'clubcore' ),
    'privacy'       => __( 'حریم خصوصی', 'clubcore' ),
    'advanced'      => __( 'پیشرفته', 'clubcore' ),
];

?>
<div class="wrap clubcore-wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'تنظیمات افزونه', 'clubcore' ); ?></h1>
    <hr class="wp-header-end">

    <h2 class="nav-tab-wrapper">
        <?php foreach ( $tabs as $tab_key => $tab_name ) : ?>
            <a href="?page=clubcore-settings&tab=<?php echo esc_attr( $tab_key ); ?>" class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                <?php echo esc_html( $tab_name ); ?>
            </a>
        <?php endforeach; ?>
    </h2>

    <div class="clubcore-settings-content">
        <?php
        $template_file = plugin_dir_path( dirname( __DIR__ ) ) . 'templates/admin/settings/' . $active_tab . '.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        } else {
            echo '<p>' . esc_html__( 'تنظیمات این بخش در حال توسعه است.', 'clubcore' ) . '</p>';
        }
        ?>
    </div>
</div>
