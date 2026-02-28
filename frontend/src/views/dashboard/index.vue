<template>
    <div>
        <Navbar />
    </div>

    <div class="p-10 min-h-screen mt-8">
        <h1 class="text-3xl ml-5 mt-5 uppercase flex font-bold items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 17.25v-.228a4.5 4.5 0 0 0-.12-1.03l-2.268-9.64a3.375 3.375 0 0 0-3.285-2.602H7.923a3.375 3.375 0 0 0-3.285 2.602l-2.268 9.64a4.5 4.5 0 0 0-.12 1.03v.228m19.5 0a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3m19.5 0a3 3 0 0 0-3-3H5.25a3 3 0 0 0-3 3m16.5 0h.008v.008h-.008v-.008Zm-3 0h.008v.008h-.008v-.008Z" />
        </svg>
        Dashboard
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-5">
            <div class="bg-blue-500 text-white p-6 rounded-lg shadow-md">
                <p class="text-3xl font-bold">{{ formatRupiah(dashboard.total_tahun) }}</p>
                <p class="text-md mt-2">Total Pendapatan Di Tahun {{ tahun }}</p>
            </div>
            <div class="bg-yellow-500 text-white p-6 rounded-lg shadow-md">
                <p class="text-3xl font-bold">{{ formatRupiah(dashboard.total_bulan) }}</p>
                <p class="text-md mt-2">Pendapatan Pada Bulan <span>{{ bulan }}</span></p>
            </div>
            <div class="bg-red-500 text-white p-6 rounded-lg shadow-md">
                <p class="text-3xl font-bold">{{ dashboard.belum_lunas }} Transaksi</p>
                <p class="text-md mt-2">Jumlah Transaksi Belum Lunas Di Tahun {{ tahun }}</p>
            </div>
            <div class="bg-green-500 text-white p-6 rounded-lg shadow-md">
                <p class="text-3xl font-bold">{{ dashboard.lunas }} Transaksi</p>
                <p class="text-md mt-2">Jumlah Transaksi Lunas Di Tahun {{  tahun }}</p>
            </div>
            <div class="bg-cyan-500 text-white p-6 rounded-lg shadow-md">
                <p class="text-3xl font-bold">{{ dashboard.jumlah_pelanggan }} Pelanggan</p>
                <p class="text-md mt-2">Jumlah Pelanggan</p>
            </div>
            <div class="bg-indigo-500 text-white p-6 rounded-lg shadow-md">
                <p class="text-3xl font-bold">{{ dashboard.jumlah_barang }} Barang</p>
                <p class="text-md mt-2">Jumlah Barang</p>
            </div>
        </div>

        <!-- Wrapper dengan background putih dan padding -->
        <div class="bg-white rounded-lg shadow-md p-6 border mt-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Line Chart -->
                <div class="relative">
                    <div class="flex items-center mb-4">
                        <h2 class="text-xl font-bold">Grafik Penjualan Tahun {{ tahun }}</h2>
                        <select v-model="tahun" @change="ambilDataDashboard" class="border ml-2 px-3 py-2 rounded-lg">
                            <option v-for="t in tahunList" :key="t" :value="t">{{ t }}</option>
                        </select>
                    </div>
                    <div class="absolute -left-14 top-1/2 transform -translate-y-1/2 -rotate-90 text-sm font-semibold text-gray-600">
                        Pendapatan
                    </div>
                    <div class="w-[700px]">
                        <LineChart :chart-data="chartData" />
                    </div>
                    <div class="text-center mt-2 mb-2 ml-28 text-md font-semibold text-gray-600">Bulan</div>
                </div>

                <!-- Pie Chart -->
                <div>
                    <h2 class="text-xl font-bold mb-4 text-center">Perbandingan Transaksi Lunas dan Belum Lunas</h2>
                    <div class="flex justify-center">
                        <div class="w-64 h-64">
                            <PieChart :chart-data="pieChartData" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, onMounted } from 'vue';
    import axios from 'axios';
    import Navbar from '@/components/Navbar.vue';
    import LineChart from '@/components/LineChart.vue';
    import BarChart from '@/components/BarChart.vue';
    import PieChart from '@/components/PieChart.vue';

    let bulan = ref("");
    const dashboard = ref({
        total_tahun: 0,
        total_bulan: 0,
        belum_lunas: 0,
        lunas: 0,
        jumlah_pelanggan: 0,
        jumlah_barang: 0
    });

    const tahunList = ref([]);
    const tahun = ref(new Date().getFullYear());
    
    const barChartData = ref({});
    const chartData = ref({
        labels: [],
        datasets: []
    });

    const pieChartData = ref({
        labels: ['Lunas', 'Belum Lunas'],
        datasets: []
    });


    const formatRupiah = (angka) => {
        if (!angka) return 'Rp0';
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(angka);
    }

    onMounted(() => {
        for (let i = 2024; i <= 2045; i++) {
            tahunList.value.push(i);
        }
        ambilDataDashboard();
    });

    const ambilDataDashboard = async () => {
        const token = localStorage.getItem('token');
            const response = await axios.get(`http://127.0.0.1:8000/api/dashboard?tahun=${tahun.value}`, {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            });
            dashboard.value = {
                total_tahun: response.data.pendapatan_tahun,
                total_bulan: response.data.pendapatan_bulan,
                belum_lunas: response.data.jumlah_belum_lunas,
                lunas: response.data.jumlah_lunas,
                jumlah_pelanggan: response.data.jumlah_pelanggan,
                jumlah_barang: response.data.jumlah_barang
            }

            bulan.value = response.data.bulan
            
            const labels = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            const dataBulanan = Array(12).fill(0);
            response.data.grafik_tahunan.forEach(item => {
                dataBulanan[item.bulan - 1] = item.total;
            });

            chartData.value = {
                labels,
                datasets: [
                    {
                        label: 'Pendapatan per Bulan',
                        data: dataBulanan,
                        fill: true,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.4
                    }
                ]
            };  
            pieChartData.value = {
                labels: ['Lunas', 'Belum Lunas'],
                datasets: [
                    {
                    label: 'Status Transaksi',
                    data: [dashboard.value.lunas, dashboard.value.belum_lunas],
                    backgroundColor: ['#4CAF51', '#F44336'],
                    borderColor: ['#388E3C', '#D32F2F'],
                    borderWidth: 1
                    }
                ]
            };
    }
</script>