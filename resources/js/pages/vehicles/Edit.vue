<script setup lang="ts">
import IndexVehicleController from '@/actions/App/Http/Controllers/Vehicle/IndexVehicleController';
import ShowVehicleController from '@/actions/App/Http/Controllers/Vehicle/ShowVehicleController';
import UpdateVehicleController from '@/actions/App/Http/Controllers/Vehicle/UpdateVehicleController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, X } from 'lucide-vue-next';
import { onMounted, ref, watch } from 'vue';

type Make = {
    id: number;
    name: string;
};

type VehicleModel = {
    id: number;
    name: string;
};

type ModelVersion = {
    id: number;
    name: string;
};

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
        vehicle_model_id: number;
        vehicle_model: {
            id: number;
            name: string;
            make_id: number;
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
    makes: Make[];
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
    {
        title: `${props.vehicle.model_version.vehicle_model.make.name} ${props.vehicle.model_version.vehicle_model.name}`,
        href: ShowVehicleController(props.vehicle.id).url,
    },
    {
        title: 'Editar',
        href: '#',
    },
];

const selectedMakeId = ref<string>(
    String(props.vehicle.model_version.vehicle_model.make_id),
);
const selectedVehicleModelId = ref<string>(
    String(props.vehicle.model_version.vehicle_model_id),
);
const vehicleModels = ref<VehicleModel[]>([]);
const modelVersions = ref<ModelVersion[]>([]);
const loadingModels = ref(false);
const loadingVersions = ref(false);
const isInitialized = ref(false);

const form = useForm({
    _method: 'PATCH',
    model_version_id: String(props.vehicle.model_version_id),
    year: props.vehicle.year,
    price: props.vehicle.price,
    mileage: props.vehicle.mileage,
    color: props.vehicle.color,
    fuel_type: props.vehicle.fuel_type,
    transmission: props.vehicle.transmission,
    description: props.vehicle.description ?? '',
    status: props.vehicle.status,
    images: [] as File[],
    remove_images: [] as number[],
});

const existingImages = ref<Media[]>([...props.vehicle.media]);

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

const loadVehicleModels = async (makeId: string) => {
    loadingModels.value = true;
    try {
        const response = await fetch(`/vehicles/data/vehicle-models/${makeId}`);
        vehicleModels.value = await response.json();
    } finally {
        loadingModels.value = false;
    }
};

const loadModelVersions = async (vehicleModelId: string) => {
    loadingVersions.value = true;
    try {
        const response = await fetch(
            `/vehicles/data/model-versions/${vehicleModelId}`,
        );
        modelVersions.value = await response.json();
    } finally {
        loadingVersions.value = false;
    }
};

onMounted(async () => {
    if (selectedMakeId.value) {
        await loadVehicleModels(selectedMakeId.value);
    }
    if (selectedVehicleModelId.value) {
        await loadModelVersions(selectedVehicleModelId.value);
    }
    isInitialized.value = true;
});

watch(selectedMakeId, async (makeId) => {
    if (!isInitialized.value) return;

    if (!makeId) {
        vehicleModels.value = [];
        modelVersions.value = [];
        selectedVehicleModelId.value = '';
        form.model_version_id = '';
        return;
    }

    await loadVehicleModels(makeId);
    selectedVehicleModelId.value = '';
    modelVersions.value = [];
    form.model_version_id = '';
});

watch(selectedVehicleModelId, async (vehicleModelId) => {
    if (!isInitialized.value) return;

    if (!vehicleModelId) {
        modelVersions.value = [];
        form.model_version_id = '';
        return;
    }

    await loadModelVersions(vehicleModelId);
    form.model_version_id = '';
});

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files) {
        form.images = Array.from(target.files);
    }
};

const removeExistingImage = (imageId: number) => {
    form.remove_images.push(imageId);
    existingImages.value = existingImages.value.filter(
        (img) => img.id !== imageId,
    );
};

const submit = () => {
    form.post(UpdateVehicleController(props.vehicle.id).url, {
        forceFormData: true,
    });
};
</script>

<template>
    <Head
        :title="`Editar ${vehicle.model_version.vehicle_model.make.name} ${vehicle.model_version.vehicle_model.name}`"
    />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link :href="ShowVehicleController(vehicle.id).url">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <h1 class="text-2xl font-bold">Editar Veículo</h1>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Identificação do Veículo</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="make">Marca</Label>
                                <Select v-model="selectedMakeId">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione a marca" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="make in makes"
                                            :key="make.id"
                                            :value="String(make.id)"
                                        >
                                            {{ make.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="vehicleModel">Modelo</Label>
                                <Select
                                    v-model="selectedVehicleModelId"
                                    :disabled="
                                        !selectedMakeId || loadingModels
                                    "
                                >
                                    <SelectTrigger>
                                        <SelectValue
                                            :placeholder="
                                                loadingModels
                                                    ? 'Carregando...'
                                                    : 'Selecione o modelo'
                                            "
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="model in vehicleModels"
                                            :key="model.id"
                                            :value="String(model.id)"
                                        >
                                            {{ model.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="modelVersion">Versão</Label>
                                <Select
                                    v-model="form.model_version_id"
                                    :disabled="
                                        !selectedVehicleModelId ||
                                        loadingVersions
                                    "
                                >
                                    <SelectTrigger>
                                        <SelectValue
                                            :placeholder="
                                                loadingVersions
                                                    ? 'Carregando...'
                                                    : 'Selecione a versão'
                                            "
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="version in modelVersions"
                                            :key="version.id"
                                            :value="String(version.id)"
                                        >
                                            {{ version.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="form.errors.model_version_id"
                                />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="year">Ano</Label>
                                <Input
                                    id="year"
                                    v-model.number="form.year"
                                    type="number"
                                    min="1900"
                                    :max="new Date().getFullYear() + 1"
                                />
                                <InputError :message="form.errors.year" />
                            </div>

                            <div class="space-y-2">
                                <Label for="color">Cor</Label>
                                <Input id="color" v-model="form.color" />
                                <InputError :message="form.errors.color" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Detalhes Técnicos</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="price">Preço (R$)</Label>
                                <Input
                                    id="price"
                                    v-model.number="form.price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                />
                                <InputError :message="form.errors.price" />
                            </div>

                            <div class="space-y-2">
                                <Label for="mileage">Quilometragem</Label>
                                <Input
                                    id="mileage"
                                    v-model.number="form.mileage"
                                    type="number"
                                    min="0"
                                />
                                <InputError :message="form.errors.mileage" />
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="fuelType">Combustível</Label>
                                <Select v-model="form.fuel_type">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="f in fuelTypes"
                                            :key="f"
                                            :value="f"
                                        >
                                            {{ getFuelTypeLabel(f) }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.fuel_type" />
                            </div>

                            <div class="space-y-2">
                                <Label for="transmission">Câmbio</Label>
                                <Select v-model="form.transmission">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="t in transmissions"
                                            :key="t"
                                            :value="t"
                                        >
                                            {{ getTransmissionLabel(t) }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError
                                    :message="form.errors.transmission"
                                />
                            </div>

                            <div class="space-y-2">
                                <Label for="status">Status</Label>
                                <Select v-model="form.status">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Selecione" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="s in statuses"
                                            :key="s"
                                            :value="s"
                                        >
                                            {{ getStatusLabel(s) }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="form.errors.status" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Descrição e Imagens</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="description">Descrição</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                placeholder="Descreva o veículo..."
                            />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div
                            v-if="existingImages.length > 0"
                            class="space-y-2"
                        >
                            <Label>Imagens Existentes</Label>
                            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                                <div
                                    v-for="image in existingImages"
                                    :key="image.id"
                                    class="group relative aspect-video overflow-hidden rounded-lg border"
                                >
                                    <img
                                        :src="image.original_url"
                                        :alt="image.name"
                                        class="h-full w-full object-cover"
                                    />
                                    <button
                                        type="button"
                                        class="absolute right-2 top-2 rounded-full bg-destructive p-1 text-destructive-foreground opacity-0 transition-opacity group-hover:opacity-100"
                                        @click="removeExistingImage(image.id)"
                                    >
                                        <X class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="images">Adicionar Imagens</Label>
                            <Input
                                id="images"
                                type="file"
                                multiple
                                accept="image/*"
                                @change="handleFileChange"
                            />
                            <p class="text-sm text-muted-foreground">
                                Você pode selecionar múltiplas imagens. Máximo
                                de 5MB por imagem.
                            </p>
                            <InputError :message="form.errors.images" />
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-end gap-4">
                    <Button variant="outline" type="button" as-child>
                        <Link :href="ShowVehicleController(vehicle.id).url">
                            Cancelar
                        </Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{
                            form.processing ? 'Salvando...' : 'Salvar Alterações'
                        }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
