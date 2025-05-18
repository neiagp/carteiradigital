<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { PieChart } from 'vue-chart-3';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    PieController,
} from 'chart.js';

// Registrar os componentes necessários do Chart.js
ChartJS.register(Title, Tooltip, Legend, ArcElement, PieController);

// Dados das transações vindos do servidor
const page = usePage();
const transacoes = ref(page.props.transacoes ?? []);

// Log para verificar os dados das transações
console.log('Transações:', transacoes.value);

// Processar os dados para o gráfico
const chartData = computed(() => {
    const categorias = {
        deposito: 0,
        transferencia: 0,
    };

    transacoes.value.forEach((t) => {
        if (t.tipo === 'deposito') {
            categorias.deposito += Number(t.valor);
        } else if (t.tipo === 'transferencia' && !t.revertida) {
            categorias.transferencia += Number(t.valor);
        }
    });

    const data = {
        labels: Object.keys(categorias),
        datasets: [
            {
                label: 'Distribuição de Transações',
                data: Object.values(categorias),
                backgroundColor: ['#36A2EB', '#FF6384'], // Azul para depósito, vermelho para transferência
            },
        ],
    };

    // Log para verificar os dados do gráfico
    console.log('chartData:', data);

    return data;
});

// KPIs
const saldoTotal = computed(() => {
    return transacoes.value.reduce((total, t) => {
        if (!t.revertida) {
            return total + Number(t.valor);
        }
        return total;
    }, 0);
});

const totalDepositos = computed(() => {
    return transacoes.value
        .filter((t) => t.tipo === 'deposito')
        .reduce((total, t) => total + Number(t.valor), 0);
});

const totalTransferencias = computed(() => {
    return transacoes.value
        .filter((t) => t.tipo === 'transferencia' && !t.revertida)
        .reduce((total, t) => total + Number(t.valor), 0);
});

// Opções do gráfico
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
    },
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- KPIs -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="p-4 bg-green-100 rounded-lg shadow">
                                <h4 class="text-lg font-semibold text-green-800">Total de Depósitos</h4>
                                <p class="text-2xl font-bold text-green-900">R$ {{ totalDepositos }}</p>
                            </div>
                            <div class="p-4 bg-red-100 rounded-lg shadow">
                                <h4 class="text-lg font-semibold text-red-800">Total de Transferências</h4>
                                <p class="text-2xl font-bold text-red-900">R$ {{ totalTransferencias }}</p>
                            </div>
                        </div>

                        <!-- Gráfico -->
                        <div class="mt-6">
                            <h3 class="text-lg font-semibold">Gráfico de Pizza - Transações</h3>
                            <div class="w-full h-[500px]">
                                <PieChart :chart-data="chartData" :chart-options="chartOptions" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
