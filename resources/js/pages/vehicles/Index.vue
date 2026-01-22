<script setup lang="ts">
import IndexVehicleController from '@/actions/App/Http/Controllers/Vehicle/IndexVehicleController';
import CreateVehicleController from '@/actions/App/Http/Controllers/Vehicle/CreateVehicleController';
import ShowVehicleController from '@/actions/App/Http/Controllers/Vehicle/ShowVehicleController';
import EditVehicleController from '@/actions/App/Http/Controllers/Vehicle/EditVehicleController';
import DestroyVehicleController from '@/actions/App/Http/Controllers/Vehicle/DestroyVehicleController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';

type Vehicle = {
    id: number;
    model_version_id: number;
    year: number;
    price: number;
    mileage: number;
    color: string;
    fuel_type: string;
    transmission: string;
    description: string | null;
    status: string;
    model_version: {
        id: number;
        name: string;
        vehicle_model: {
            id: number;
            name: string;
            make: {
                id: number;
                name: string;
            };
        };
    };
};

type PaginatedData<T> = {
    data: T[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};

type Props = {
    vehicles: PaginatedData<Vehicle>;
    filters: {
        search?: string;
        status?: string;
        fuel_type?: string;
        transmission?: string;
    };
    fuelTypes: string[];
    transmissions: string[];
    statuses: string[];
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Veículos',
        href: IndexVehicleController().url,
    },
];

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? 'all');
const fuelType = ref(props.filters.fuel_type ?? 'all');
const transmission = ref(props.filters.transmission ?? 'all');

const deleteDialogOpen = ref(false);
const vehicleToDelete = ref<Vehicle | null>(null);

const applyFilters = () => {
    router.get(
        IndexVehicleController().url,
        {
            search: search.value || undefined,
            status: status.value === 'all' ? undefined : status.value,
            fuel_type: fuelType.value === 'all' ? undefined : fuelType.value,
            transmission: transmission.value === 'all' ? undefined : transmission.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

watch([search], () => {
    const timeout = setTimeout(() => {
        applyFilters();
    }, 300);
    return () => clearTimeout(timeout);
});

watch([status, fuelType, transmission], () => {
    applyFilters();
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(price);
};

const formatMileage = (mileage: number) => {
    return new Intl.NumberFormat('pt-BR').format(mileage) + ' km';
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'active':
            return 'default';
        case 'sold':
            return 'secondary';
        case 'paused':
            return 'outline';
        default:
            return 'default';
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'active':
            return 'Ativo';
        case 'sold':
            return 'Vendido';
        case 'paused':
            return 'Pausado';
        default:
            return status;
    }
};

const getFuelTypeLabel = (fuelType: string) => {
    const labels: Record<string, string> = {
        gasoline: 'Gasolina',
        ethanol: 'Etanol',
        flex: 'Flex',
        diesel: 'Diesel',
        electric: 'Elétrico',
        hybrid: 'Híbrido',
    };
    return labels[fuelType] ?? fuelType;
};

const getTransmissionLabel = (transmission: string) => {
    const labels: Record<string, string> = {
        manual: 'Manual',
        automatic: 'Automático',
        cvt: 'CVT',
        automated: 'Automatizado',
    };
    return labels[transmission] ?? transmission;
};

const confirmDelete = (vehicle: Vehicle) => {
    vehicleToDelete.value = vehicle;
    deleteDialogOpen.value = true;
};

const deleteVehicle = () => {
    if (vehicleToDelete.value) {
        router.delete(DestroyVehicleController(vehicleToDelete.value.id).url, {
            onSuccess: () => {
                deleteDialogOpen.value = false;
                vehicleToDelete.value = null;
            },
        });
    }
};

const goToPage = (url: string | null) => {
    if (url) {
        router.get(url, {}, { preserveState: true });
    }
};
</script>

<template>
    <Head title="Veículos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Veículos</h1>
                <Button as-child>
                    <Link :href="CreateVehicleController().url">
                        <Plus class="mr-2 h-4 w-4" />
                        Novo Veículo
                    </Link>
                </Button>
            </div>

            <div class="flex flex-wrap gap-4">
                <Input
                    v-model="search"
                    placeholder="Buscar por marca, modelo, versão ou cor..."
                    class="max-w-sm"
                />

                <Select v-model="status">
                    <SelectTrigger class="w-45">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todos</SelectItem>
                        <SelectItem
                            v-for="s in statuses"
                            :key="s"
                            :value="s"
                        >
                            {{ getStatusLabel(s) }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="fuelType">
                    <SelectTrigger class="w-45">
                        <SelectValue placeholder="Combustível" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todos</SelectItem>
                        <SelectItem
                            v-for="f in fuelTypes"
                            :key="f"
                            :value="f"
                        >
                            {{ getFuelTypeLabel(f) }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="transmission">
                    <SelectTrigger class="w-45">
                        <SelectValue placeholder="Câmbio" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todos</SelectItem>
                        <SelectItem
                            v-for="t in transmissions"
                            :key="t"
                            :value="t"
                        >
                            {{ getTransmissionLabel(t) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Veículo</TableHead>
                            <TableHead>Ano</TableHead>
                            <TableHead>Preço</TableHead>
                            <TableHead>Km</TableHead>
                            <TableHead>Combustível</TableHead>
                            <TableHead>Câmbio</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Ações</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="vehicle in vehicles.data"
                            :key="vehicle.id"
                        >
                            <TableCell class="font-medium">
                                <div>
                                    {{
                                        vehicle.model_version.vehicle_model.make
                                            .name
                                    }}
                                    {{
                                        vehicle.model_version.vehicle_model.name
                                    }}
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    {{ vehicle.model_version.name }} -
                                    {{ vehicle.color }}
                                </div>
                            </TableCell>
                            <TableCell>{{ vehicle.year }}</TableCell>
                            <TableCell>{{
                                formatPrice(vehicle.price)
                            }}</TableCell>
                            <TableCell>{{
                                formatMileage(vehicle.mileage)
                            }}</TableCell>
                            <TableCell>{{
                                getFuelTypeLabel(vehicle.fuel_type)
                            }}</TableCell>
                            <TableCell>{{
                                getTransmissionLabel(vehicle.transmission)
                            }}</TableCell>
                            <TableCell>
                                <Badge :variant="getStatusVariant(vehicle.status)">
                                    {{ getStatusLabel(vehicle.status) }}
                                </Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        as-child
                                    >
                                        <Link
                                            :href="
                                                ShowVehicleController(
                                                    vehicle.id,
                                                ).url
                                            "
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        as-child
                                    >
                                        <Link
                                            :href="
                                                EditVehicleController(
                                                    vehicle.id,
                                                ).url
                                            "
                                        >
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="confirmDelete(vehicle)"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="vehicles.data.length === 0">
                            <TableCell colspan="8" class="text-center py-8">
                                Nenhum veículo encontrado.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div
                v-if="vehicles.last_page > 1"
                class="flex items-center justify-between"
            >
                <p class="text-sm text-muted-foreground">
                    Mostrando {{ vehicles.data.length }} de
                    {{ vehicles.total }} veículos
                </p>
                <div class="flex gap-2">
                    <Button
                        v-for="link in vehicles.links"
                        :key="link.label"
                        :variant="link.active ? 'default' : 'outline'"
                        :disabled="!link.url"
                        size="sm"
                        @click="goToPage(link.url)"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>

        <Dialog v-model:open="deleteDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Confirmar exclusão</DialogTitle>
                    <DialogDescription>
                        Tem certeza que deseja excluir o veículo
                        <strong v-if="vehicleToDelete">
                            {{
                                vehicleToDelete.model_version.vehicle_model.make
                                    .name
                            }}
                            {{ vehicleToDelete.model_version.vehicle_model.name }}
                            {{ vehicleToDelete.model_version.name }}
                            {{ vehicleToDelete.year }}
                        </strong>
                        ? Esta ação não pode ser desfeita.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button
                        variant="outline"
                        @click="deleteDialogOpen = false"
                    >
                        Cancelar
                    </Button>
                    <Button variant="destructive" @click="deleteVehicle">
                        Excluir
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
