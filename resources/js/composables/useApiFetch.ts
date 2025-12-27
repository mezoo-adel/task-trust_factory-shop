import { ref } from 'vue';
import { useToast } from '@/components/ui/toast/use-toast';

interface ApiResponse<T = any> {
    data?: T;
    message?: string;
    errors?: Record<string, string | string[]>;
    [key: string]: any;
}

interface FetchOptions {
    method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
    body?: Record<string, any> | FormData;
    headers?: Record<string, string>;
    showSuccessToast?: boolean;
    showErrorToast?: boolean;
    successMessage?: string;
    errorMessage?: string;
}

export function useApiFetch() {
    const { toast } = useToast();
    const isLoading = ref(false);
    const error = ref<string | null>(null);
    const errors = ref<Record<string, string | string[]>>({});

    const getCsrfToken = (): string | null => {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || null;
    };

    const apiFetch = async <T = any>(
        url: string,
        options: FetchOptions = {}
    ): Promise<ApiResponse<T> | null> => {
        const {
            method = 'GET',
            body,
            headers = {},
            showSuccessToast = false,
            showErrorToast = true,
            successMessage,
            errorMessage,
        } = options;

        isLoading.value = true;
        error.value = null;
        errors.value = {};

        try {
            const csrfToken = getCsrfToken();
            
            const fetchHeaders: Record<string, string> = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...headers,
            };

            let fetchBody: FormData | string | undefined;

            if (body) {
                if (body instanceof FormData) {
                    fetchBody = body;
                    if (csrfToken) {
                        body.append('_token', csrfToken);
                    }
                } else {
                    fetchHeaders['Content-Type'] = 'application/json';
                    fetchBody = JSON.stringify(body);
                    
                    // Add CSRF token to JSON body
                    if (csrfToken && method !== 'GET') {
                        const bodyObj = { ...body, _token: csrfToken };
                        fetchBody = JSON.stringify(bodyObj);
                    }
                }
            }

            const response = await fetch(url, {
                method,
                headers: fetchHeaders,
                credentials: 'include',
                body: fetchBody,
            });

            const data: ApiResponse<T> = await response.json().catch(() => ({}));

            if (!response.ok) {
                error.value = data.message || errorMessage || 'An error occurred';
                errors.value = data.errors || {};

                if (showErrorToast) {
                    toast({
                        title: 'Error',
                        description: error.value,
                        variant: 'destructive',
                    });
                }

                return null;
            }

            if (showSuccessToast) {
                toast({
                    title: 'Success',
                    description: successMessage || data.message || 'Operation completed successfully',
                });
            }

            return data;
        } catch (err) {
            const errorMsg = errorMessage || 'An unexpected error occurred';
            error.value = errorMsg;

            if (showErrorToast) {
                toast({
                    title: 'Error',
                    description: errorMsg,
                    variant: 'destructive',
                });
            }

            console.error('API Fetch Error:', err);
            return null;
        } finally {
            isLoading.value = false;
        }
    };

    return {
        apiFetch,
        isLoading,
        error,
        errors,
    };
}

