<script setup lang="ts">
import IndexVehicleController from '@/actions/App/Http/Controllers/Vehicle/IndexVehicleController';
import EditVehicleController from '@/actions/App/Http/Controllers/Vehicle/EditVehicleController';
import DestroyVehicleController from '@/actions/App/Http/Controllers/Vehicle/DestroyVehicleController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import { ArrowLeft, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

type Media = {
    id: number;
    original_url: string;
    name: string;
};

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
    media: Media[];
};

type Props = {
    vehicle: Vehicle;
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Veículos',
        href: IndexVehicleController().url,
    },
    {
        title: `${props.vehicle.model_version.vehicle_model.make.name} ${props.vehicle.model_version.vehicle_model.name}`,
        href: '#',
    },
];

const deleteDialogOpen = ref(false);

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

const deleteVehicle = () => {
    router.delete(DestroyVehicleController(props.vehicle.id).url);
};
</script>

<template>
    <Head
        :title="`${vehicle.model_version.vehicle_model.make.name} ${vehicle.model_version.vehicle_model.name}`"
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="IndexVehicleController().url">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold">
                            {{ vehicle.model_version.vehicle_model.make.name }}
                            {{ vehicle.model_version.vehicle_model.name }}
                        </h1>
                        <p class="text-muted-foreground">
                            {{ vehicle.model_version.name }} - {{ vehicle.year }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="EditVehicleController(vehicle.id).url">
                            <Pencil class="mr-2 h-4 w-4" />
                            Editar
                        </Link>
                    </Button>
                    <Button
                        variant="destructive"
                        @click="deleteDialogOpen = true"
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Excluir
                    </Button>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Informações do Veículo</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Marca
                                </p>
                                <p class="text-lg">
                                    {{
                                        vehicle.model_version.vehicle_model.make
                                            .name
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Modelo
                                </p>
                                <p class="text-lg">
                                    {{
                                        vehicle.model_version.vehicle_model.name
                                    }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Versão
                                </p>
                                <p class="text-lg">
                                    {{ vehicle.model_version.name }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Ano
                                </p>
                                <p class="text-lg">{{ vehicle.year }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Cor
                                </p>
                                <p class="text-lg">{{ vehicle.color }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Status
                                </p>
                                <Badge :variant="getStatusVariant(vehicle.status)">
                                    {{ getStatusLabel(vehicle.status) }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Detalhes Técnicos</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Preço
                                </p>
                                <p class="text-lg font-semibold text-green-600">
                                    {{ formatPrice(vehicle.price) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Quilometragem
                                </p>
                                <p class="text-lg">
                                    {{ formatMileage(vehicle.mileage) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Combustível
                                </p>
                                <p class="text-lg">
                                    {{ getFuelTypeLabel(vehicle.fuel_type) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">
                                    Câmbio
                                </p>
                                <p class="text-lg">
                                    {{
                                        getTransmissionLabel(
                                            vehicle.transmission,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card class="md:col-span-2" v-if="vehicle.description">
                    <CardHeader>
                        <CardTitle>Descrição</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="whitespace-pre-wrap">
                            {{ vehicle.description }}
                        </p>
                    </CardContent>
                </Card>

                <Card class="md:col-span-2" v-if="vehicle.media.length > 0">
                    <CardHeader>
                        <CardTitle>Imagens</CardTitle>
                        <CardDescription>
                            {{ vehicle.media.length }} imagem(ns)
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                            <div
                                v-for="media in vehicle.media"
                                :key="media.id"
                                class="relative aspect-video overflow-hidden rounded-lg border"
                            >
                                <img
                                    :src="media.original_url"
                                    :alt="media.name"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <Dialog v-model:open="deleteDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Confirmar exclusão</DialogTitle>
                    <DialogDescription>
                        Tem certeza que deseja excluir o veículo
                        <strong>
                            {{ vehicle.model_version.vehicle_model.make.name }}
                            {{ vehicle.model_version.vehicle_model.name }}
                            {{ vehicle.model_version.name }}
                            {{ vehicle.year }}
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
