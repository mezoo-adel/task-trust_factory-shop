export interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
}

export interface Upload {
    id: number;
    file_name: string;
    file_path: string;
    url: string;
    file_size: number;
    mime_type: string;
}

export interface UploadProgress {
    uploadId: string;
    progress: number;
    file: File;
}

// Product Types
export interface Product {
    id: number;
    name: string;
    slug: string;
    description: string;
    price: number;
    stock_quantity: number;
    stock_threshold: number;
    is_active: boolean;
    image_urls: string[];
}

// Cart Types
export interface CartItem {
    id: number;
    product_id: number;
    quantity: number;
    discount: number;
    total: number;
    product: {
        id: number;
        name: string;
        slug: string;
        price: number;
        stock_quantity?: number;
    };
}

export interface Cart {
    id: number;
    discount: number;
    tax: number;
    subtotal: number;
    sub_total?: number; // Backend sometimes uses snake_case
    total: number;
    items: CartItem[];
}

// Address Types
export interface Address {
    id: number;
    label?: string | null;
    full_name: string | null;
    phone: string;
    address: string;
    is_default: boolean;
}

// Order Types
export interface OrderItem {
    id: number;
    product_id: number;
    price: number;
    quantity: number;
    subtotal: number;
    discount: number;
    tax: number;
    total: number;
    product: {
        id: number;
        name: string;
        slug: string;
    };
}

export interface Order {
    id: number;
    uuid: string;
    status: string;
    subtotal: number;
    tax: number;
    shipping: number;
    total: number;
    stripe_payment_intent_id?: string | null;
    notes?: string | null;
    created_at: string;
    items: OrderItem[];
    address?: Address;
}

// Filter Types
export interface ProductFilters {
    search?: string;
    sort?: string;
    inStock?: boolean;
}

// Pagination Types
export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: PaginationLink[];
}

// Admin Types
export interface AdminDashboardStats {
    today_orders: number;
    today_revenue: number;
    week_orders: number;
    month_orders: number;
    total_revenue: number;
    total_users: number;
    total_products: number;
    low_stock_count: number;
}

export interface OrdersByStatus {
    paid: number;
    processing: number;
    shipped: number;
    delivered: number;
    cancelled: number;
}

export interface RecentOrder {
    id: number;
    uuid: string;
    user_name: string;
    user_email: string;
    status: string;
    total: number;
    items_count: number;
    created_at: string;
}

export interface LowStockProduct {
    id: number;
    name: string;
    slug: string;
    stock_quantity: number;
    stock_threshold: number;
    price: number;
}

export interface AdminUser {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    created_at: string;
    updated_at: string;
}

export interface ProfileProps {
    user: User;
    addresses: Address[];
    notification_preferences: Record<string, boolean>;
}
