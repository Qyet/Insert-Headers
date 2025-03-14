<?php
/*
Plugin Name: Insert Headers
Author: Qyet
Description: 自定义插入HTTP标头、Meta标签和全局脚本（已修复致命错误）
Version: 1.1
*/

// 安全检测：防止直接访问
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 创建后台设置页面
function ih_create_admin_menu() {
    add_options_page(
        'Insert Headers 设置',
        'Insert Headers',
        'manage_options',
        'insert-headers',
        'ih_settings_page'
    );
}
add_action( 'admin_menu', 'ih_create_admin_menu' );

// 设置页面内容
function ih_settings_page() {
    ?>
    <div class="wrap">
        <h2>Insert Headers 配置</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'ih_settings_group' );
            do_settings_sections( 'insert-headers' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// 注册设置项
function ih_register_settings() {
    register_setting( 'ih_settings_group', 'ih_meta_tags' );
    register_setting( 'ih_settings_group', 'ih_global_scripts' );
    
    add_settings_section(
        'ih_main_section',
        '自定义内容',
        null,
        'insert-headers'
    );

    add_settings_field(
        'ih_meta_tags',
        'Meta 标签',
        'ih_meta_tags_callback',
        'insert-headers',
        'ih_main_section'
    );

    add_settings_field(
        'ih_global_scripts',
        '全局脚本',
        'ih_scripts_callback',
        'insert-headers',
        'ih_main_section'
    );
}
add_action( 'admin_init', 'ih_register_settings' );

// 输入字段回调函数
function ih_meta_tags_callback() {
    $content = get_option( 'ih_meta_tags' );
    echo '<textarea name="ih_meta_tags" rows="5" style="width:100%">' 
         . esc_textarea( $content ) 
         . '</textarea>';
}

function ih_scripts_callback() {
    $content = get_option( 'ih_global_scripts' );
    echo '<textarea name="ih_global_scripts" rows="10" style="width:100%">' 
         . esc_textarea( $content ) 
         . '</textarea>';
}

// 输出到前端
function ih_output_headers() {
    // 输出Meta标签
    if ( $meta = get_option( 'ih_meta_tags' ) ) {
        echo wp_kses( $meta, array( 
            'meta' => array(
                'name'    => array(),
                'content' => array(),
                'property' => array(),
                'charset' => array()
            )
        )) . "\n";
    }
    
    // 输出全局脚本（排除管理员）
    if ( ! current_user_can( 'manage_options' ) && ( $scripts = get_option( 'ih_global_scripts' ) ) ) {
        echo wp_kses( $scripts, array( 
            'script' => array(
                'src'   => array(),
                'async' => array(),
                'defer' => array()
            ),
            'noscript' => array()
        )) . "\n";
    }
}
add_action( 'wp_head', 'ih_output_headers', 2 );
