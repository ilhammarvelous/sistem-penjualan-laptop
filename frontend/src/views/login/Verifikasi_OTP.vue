<template>
    <div class="flex justify-center items-center min-h-screen bg-gray-100">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96 text-center">
            <h2 class="text-xl font-bold mb-4">Verifikasi Kode OTP</h2>
            <p class="text-gray-600 mb-4">Masukkan kode OTP yang dikirim ke WhatsApp</p>

            <div class="flex justify-center space-x-2 mb-4">
                <input
                    v-for="(digit, index) in otp"
                    :id="'otp' + index"
                    :key="index"
                    type="text"
                    maxlength="1"
                    v-model="otp[index]"
                    class="w-12 h-12 text-center text-lg border border-gray-300 rounded focus:outline-none focus:border-blue-500"
                    @input="focusNext(index)"
                />
            </div>

            <div v-if="timer > 0" class="text-sm text-gray-500 mb-4">
                Waktu tersisa : {{ formatTime }} 
            </div>

            <button
                class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 transition"
                @click="verifyOtp"
                :disabled="!isOtpComplete || timer === 0">
                Verifikasi
            </button>

            <p class="text-gray-500 text-sm mt-4">
            Tidak menerima kode? 
                <button class="text-blue-500 hover:underline" 
                    @click="kirimUlangOtp" 
                    :disabled="isResendDisabled">
                    Kirim Ulang OTP
                </button>
            </p>

        </div>
    </div>
</template>


<script>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRouter } from 'vue-router'
import axios from "axios";

export default {
    setup() {
        const router = useRouter()
        const otp = ref(["", "", "", "", "", ""]);
        const timer = ref(60); 
        const isResendDisabled = ref(false);
        const isOtpComplete = computed(() => otp.value.join("").length === 6);
        let interval = null;
    
        const focusNext = (index) => {
            if (otp.value[index] !== "" && index < 5) {
            document.getElementById(`otp${index + 1}`).focus();
            }
        };

        // Verifikasi OTP
        const verifyOtp = async () => {
            const otpCode = otp.value.join("");

            try {
                const token = localStorage.getItem('token');
                
                if (!token) {
                    swal('Error', 'Token tidak ditemukan. Anda perlu login terlebih dahulu.', 'error');
                    return;
                }

                const response = await axios.post(`http://127.0.0.1:8000/api/otp/verifikasi-otp`,
                    {otp_code: otpCode},
                    {
                        headers: {
                            Authorization: `Bearer ${token}`,
                        },
                    }
                );
                
                if (response.data.success) {
                    swal({
                        title: "Berhasil",
                        text: "OTP berhasil diverifikasi!",
                        icon: "success",
                        showConfirmButton: true,
                        timer: 1500
                    }).then(() => {
                        router.push({ name: 'dashboard' });
                        localStorage.removeItem('OTP');
                    });
                }
            } catch (err) {
                swal({
                    title: "Error!",
                    icon: "error",
                    text: err.response?.data?.message || "Terjadi kesalahan saat verifikasi OTP. Coba lagi!",
                    showConfirmButton: true,
                    timer: 2000
                });
                otp.value = ["", "", "", "", "", ""];
            }
        };

        const formatTime = computed(() => {
            const menit = Math.floor(timer.value / 60);
            const detik = timer.value % 60;
            return `${menit.toString().padStart(2, '0')}:${detik.toString().padStart(2, '0')}`
        });

        const startTimer = () => {
            clearInterval(interval);
            interval = setInterval(() => {
                if (timer.value > 0) {
                    timer.value -= 1;
                } else {
                    clearInterval(interval);
                    swal({
                        title: "Error!",
                        icon: "error",
                        text: "Waktu habis! Anda harus mengirim ulang kode OTP.",
                        showConfirmButton: true,
                        timer: 1500
                    });
                }
            }, 1000);
        };

        onUnmounted(() => {
            if(interval){
                clearInterval(interval);
            }
        });

        onMounted(() => {
            startTimer();
        });

        const kirimUlangOtp = async () => {
            try {
                const token = localStorage.getItem('token');
                if(!token){
                    swal('Anda harus login terlebih dahulu!');
                    return;
                }

                const response = await axios.post(`http://127.0.0.1:8000/api/otp/kirim-ulang`, {},  {
                    headers: {
                        Authorization: `Bearer ${token}`,
                    },
                });

                otp.value = ["", "", "", "", "", ""];

                if (response.data.success) {
                    swal({
                        title: "Berhasil",
                        icon: "success",
                        text: response.data.message,
                        showConfirmButton: true,
                        timer: 1500
                    })

                    clearInterval(interval);
                    timer.value = 60;
                    startTimer();
                    
                    isResendDisabled.value = true;
                    setTimeout(() => {
                        isResendDisabled.value = false;
                    }, 60000);
                } else {
                    swal({
                        title: "Error",
                        icon: "error",
                        text: response.data.message,
                        showConfirmButton: true,
                        timer: 1500
                    })
                }
            } catch (err) {
                swal('Error', err.response?.data?.message || 'Gagal mengirim ulang OTP');
                otp.value = ["", "", "", "", "", ""];
            }
        };

        return {
            otp,
            isOtpComplete,
            focusNext,
            verifyOtp,
            kirimUlangOtp,
            isResendDisabled,
            timer,
            formatTime,
        };
    }
};
</script>

<style scoped>
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>