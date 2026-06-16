<?php
declare(strict_types=1);

/**
 * Declarative definitions for the admin CRUD engine. Each resource lists the
 * fields it exposes; field "type" drives both rendering and server-side
 * sanitisation. This keeps every content type consistent and secure.
 *
 * Field types: text, textarea, richtext, image, number, checkbox, select, email
 */

return [
    'hero' => [
        'table' => 'hero_slides',
        'label' => 'Hero Slides',
        'singular' => 'Hero Slide',
        'icon' => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>',
        'order' => 'sort_order, id',
        'list_columns' => ['title' => 'Title', 'eyebrow' => 'Eyebrow', 'sort_order' => 'Order', 'is_active' => 'Active'],
        'fields' => [
            'title'      => ['type' => 'text', 'label' => 'Title', 'required' => true],
            'eyebrow'    => ['type' => 'text', 'label' => 'Eyebrow (small label above title)'],
            'body'       => ['type' => 'textarea', 'label' => 'Body text'],
            'image_path' => ['type' => 'image', 'label' => 'Background image'],
            'sort_order' => ['type' => 'number', 'label' => 'Sort order'],
            'is_active'  => ['type' => 'checkbox', 'label' => 'Active (visible on site)', 'default' => 1],
        ],
    ],

    'services' => [
        'table' => 'services',
        'label' => 'Services',
        'singular' => 'Service',
        'icon' => '<path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>',
        'order' => 'sort_order, title',
        'list_columns' => ['title' => 'Title', 'featured' => 'Featured', 'sort_order' => 'Order', 'is_active' => 'Active'],
        'fields' => [
            'title'             => ['type' => 'text', 'label' => 'Title', 'required' => true],
            'slug'              => ['type' => 'slug', 'label' => 'Slug (URL key)', 'source' => 'title'],
            'short_description' => ['type' => 'textarea', 'label' => 'Short description (shown on cards)', 'required' => true],
            'description'       => ['type' => 'richtext', 'label' => 'Full description'],
            'image_path'        => ['type' => 'image', 'label' => 'Service image'],
            'featured'          => ['type' => 'checkbox', 'label' => 'Featured'],
            'sort_order'        => ['type' => 'number', 'label' => 'Sort order'],
            'is_active'         => ['type' => 'checkbox', 'label' => 'Active', 'default' => 1],
        ],
    ],

    'news' => [
        'table' => 'news_posts',
        'label' => 'News & Insights',
        'singular' => 'Post',
        'icon' => '<path d="M4 22h16a2 2 0 002-2V4a2 2 0 00-2-2H8a2 2 0 00-2 2v16a2 2 0 01-2 2z"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8z"/>',
        'order' => 'published_at DESC',
        'list_columns' => ['title' => 'Title', 'category' => 'Category', 'published' => 'Published'],
        'fields' => [
            'title'        => ['type' => 'text', 'label' => 'Title', 'required' => true],
            'slug'         => ['type' => 'slug', 'label' => 'Slug', 'source' => 'title'],
            'excerpt'      => ['type' => 'textarea', 'label' => 'Excerpt', 'required' => true],
            'body'         => ['type' => 'richtext', 'label' => 'Article body'],
            'category'     => ['type' => 'text', 'label' => 'Category', 'default' => 'Company News'],
            'image_path'   => ['type' => 'image', 'label' => 'Cover image'],
            'published'    => ['type' => 'checkbox', 'label' => 'Published', 'default' => 1],
            'published_at' => ['type' => 'datetime', 'label' => 'Publish date'],
        ],
    ],

    'testimonials' => [
        'table' => 'testimonials',
        'label' => 'Testimonials',
        'singular' => 'Testimonial',
        'icon' => '<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>',
        'order' => 'id DESC',
        'list_columns' => ['client_name' => 'Client', 'company' => 'Company', 'rating' => 'Rating', 'is_active' => 'Active'],
        'fields' => [
            'client_name'  => ['type' => 'text', 'label' => 'Client name', 'required' => true],
            'company'      => ['type' => 'text', 'label' => 'Company / role'],
            'country_flag' => ['type' => 'text', 'label' => 'Country flag emoji (e.g. 🇰🇪)'],
            'quote'        => ['type' => 'textarea', 'label' => 'Quote', 'required' => true],
            'rating'       => ['type' => 'number', 'label' => 'Rating (1–5)', 'default' => 5, 'min' => 1, 'max' => 5],
            'is_active'    => ['type' => 'checkbox', 'label' => 'Active', 'default' => 1],
        ],
    ],

    'team' => [
        'table' => 'team_members',
        'label' => 'Team Members',
        'singular' => 'Team Member',
        'icon' => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>',
        'order' => 'sort_order, full_name',
        'list_columns' => ['full_name' => 'Name', 'role' => 'Role', 'sort_order' => 'Order', 'is_active' => 'Active'],
        'fields' => [
            'full_name'  => ['type' => 'text', 'label' => 'Full name', 'required' => true],
            'role'       => ['type' => 'text', 'label' => 'Role / title'],
            'bio'        => ['type' => 'textarea', 'label' => 'Short bio'],
            'phone'      => ['type' => 'text', 'label' => 'Phone'],
            'email'      => ['type' => 'email', 'label' => 'Email'],
            'image_path' => ['type' => 'image', 'label' => 'Photo'],
            'sort_order' => ['type' => 'number', 'label' => 'Sort order'],
            'is_active'  => ['type' => 'checkbox', 'label' => 'Active', 'default' => 1],
        ],
    ],

    'shipments' => [
        'table' => 'shipments',
        'label' => 'Shipments (Tracking)',
        'singular' => 'Shipment',
        'icon' => '<rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
        'order' => 'updated_at DESC',
        'list_columns' => ['reference_number' => 'Reference', 'customer_name' => 'Customer', 'status' => 'Status'],
        'fields' => [
            'reference_number' => ['type' => 'text', 'label' => 'Reference number', 'required' => true],
            'customer_name'    => ['type' => 'text', 'label' => 'Customer name'],
            'origin'           => ['type' => 'text', 'label' => 'Origin'],
            'destination'      => ['type' => 'text', 'label' => 'Destination'],
            'current_stage'    => ['type' => 'text', 'label' => 'Current stage'],
            'status'           => ['type' => 'select', 'label' => 'Status', 'options' => ['In Transit', 'Booked', 'At Port', 'Customs', 'Out for Delivery', 'Delivered', 'On Hold'], 'default' => 'In Transit'],
            'stages'           => ['type' => 'stages', 'label' => 'Tracking stages'],
        ],
    ],

    'pages' => [
        'table' => 'pages',
        'label' => 'Static Pages',
        'singular' => 'Page',
        'icon' => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/>',
        'order' => 'title',
        'list_columns' => ['title' => 'Title', 'slug' => 'URL', 'is_active' => 'Active'],
        'fields' => [
            'title'            => ['type' => 'text', 'label' => 'Title', 'required' => true],
            'slug'             => ['type' => 'slug', 'label' => 'Slug (URL key, e.g. about)', 'source' => 'title'],
            'meta_description' => ['type' => 'textarea', 'label' => 'Meta description (SEO)'],
            'body'             => ['type' => 'richtext', 'label' => 'Page content'],
            'is_active'        => ['type' => 'checkbox', 'label' => 'Active (visible)', 'default' => 1],
        ],
    ],

    'menu' => [
        'table' => 'menu_items',
        'label' => 'Navigation Menu',
        'singular' => 'Menu Item',
        'icon' => '<line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>',
        'order' => 'parent_id IS NULL DESC, parent_id, sort_order',
        'list_columns' => ['title' => 'Label', 'url' => 'Link', 'parent_id' => 'Parent', 'sort_order' => 'Order', 'is_active' => 'Active'],
        'fields' => [
            'title'        => ['type' => 'text', 'label' => 'Label', 'required' => true],
            'parent_id'    => ['type' => 'parent', 'label' => 'Parent (leave as top-level for a mega-menu heading)'],
            'subtitle'     => ['type' => 'text', 'label' => 'Subtitle (small text under a dropdown link)'],
            'url'          => ['type' => 'text', 'label' => 'Link URL (e.g. /services/air-freight)', 'default' => '#'],
            'icon'         => ['type' => 'textarea', 'label' => 'Icon SVG inner paths (optional)'],
            'is_mega'      => ['type' => 'checkbox', 'label' => 'Show children as a mega-menu (top-level only)'],
            'column_group' => ['type' => 'number', 'label' => 'Mega column (1, 2 or 3)', 'default' => 1, 'min' => 1, 'max' => 3],
            'sort_order'   => ['type' => 'number', 'label' => 'Sort order'],
            'is_active'    => ['type' => 'checkbox', 'label' => 'Active', 'default' => 1],
        ],
    ],
];
