import { usePage } from '@inertiajs/vue3';

export default function (): string {
    // Try to get CSRF token from Inertia page props first
    const page = usePage();
    if (
        page.props &&
        typeof page.props === 'object' &&
        'csrf_token' in page.props
    ) {
        return page.props.csrf_token as string;
    }

    // Fallback to meta tag
    const metaToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    if (metaToken) {
        return metaToken;
    }

    console.warn('CSRF token not found in page props or meta tag');
    return '';
}
