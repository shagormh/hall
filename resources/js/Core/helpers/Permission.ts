import { usePage } from '@inertiajs/vue3';

export const checkPermission = (permission: string) => {
    const page = usePage();
    const allPermissions = (page.props.auth as any).permissions || [];
    return allPermissions.some((element: any) => {
        return element.name === permission;
    });
}
