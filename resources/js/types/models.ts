// Product Types
export interface Product {
    id: number;
    name: string;
    description: string;
    price: number;
    stock_quantity: number;
    stock_threshold: number;
    is_active: boolean;
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

