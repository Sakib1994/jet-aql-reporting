<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Daily Ads Info
      </h2>
      <div class="relative">
        <div class="absolute -top-9 right-0 mx-2">
          <!-- absolute -top-7 right-0 -->
          <inertia-link
            :href="route('hourly-ads.fetchhourly')"
            class="inline-block bg-indigo-500 hover:bg-indigo-400 focus:outline-none focus:ring focus:ring-offset-2 focus:ring-indigo-500 focus:ring-opacity-50 text-white px-5 py-3 hover:-translate-y-0.5 transform transition active:bg-indigo-600 rounded-lg shadow-lg uppercase tracking-wider font-semibold text-sm"
          >
            Add Yahoo data
          </inertia-link>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <table class="table-auto mx-auto">
              <thead>
                <tr class="text-sm font-light">
                  <th class="px-1">日付</th>
                  <th class="px-1">time</th>
                  <th class="px-1">Account Name</th>
                  <th class="px-1">表示回数</th>
                  <th class="px-1">クリック数</th>
                  <th class="px-1">クリック率</th>
                  <th class="px-1">クリック単価</th>
                  <th class="px-1">費用</th>
                  <th class="px-1">コンバージョン</th>
                  <th class="px-1">コンバージョン率</th>
                  <th class="px-1">コンバージョン単価</th>
                  <th class="px-1">編集</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(data, index) in hourlyAds.data"
                  :key="index"
                  :class="[
                    index % 2 != 0
                      ? 'bg-green-100 hover:bg-gray-200'
                      : 'hover:bg-gray-200',
                  ]"
                >
                  <td class="px-2">{{ formatDate(data.time) }}</td>
                  <td class="px-2">{{ formatTime(data.time) }}</td>
                  <td class="px-2">{{ data.name }}</td>
                  <td class="px-2">{{ data.impressions }}</td>
                  <td class="px-2">{{ data.clicks }}</td>
                  <td class="px-2">{{ data.ctr }}%</td>
                  <td class="px-2">{{ formatPrice(data.cpc) }}</td>
                  <td class="px-2">{{ formatPrice(data.cost) }}</td>
                  <td class="px-2">{{ data.conversions }}</td>
                  <td class="px-2">{{ data.conversions_rate }}%</td>
                  <td class="px-2">{{ formatPrice(data.cost_per_conversion) }}</td>
                  <td class="px-2 flex flex-row space-x-2">
                    <inertia-link
                      :href="route('hourly-ads.edit', data.id)"
                      class="text-blue-600"
                    >
                      <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-7 w-7"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                      >
                        <path
                          d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"
                        />
                      </svg>
                    </inertia-link>
                  </td>
                </tr>
              </tbody>
            </table>
            <Pagination
              class="mt-6"
              :links="hourlyAds.links"
            />
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout'
import Pagination from "@/Components/Pagination";
export default {
  components: {
    AppLayout,
    Pagination,
  },
  methods:{
    formatPrice(value) {
      const formatter = new Intl.NumberFormat("en-US", { style: "currency", currency: "JPY",
        maximumFractionDigits: 0,
      });
      return formatter.format(value);
    },
    formatDate(datetime){
      const formatedDatetime = new Date(datetime);
      return formatedDatetime.toLocaleDateString('en-US');
    },
    formatTime(datetime){
      const formatedDatetime = new Date(datetime);
      return formatedDatetime.toLocaleTimeString('en-US');
    }
  },
  props: {
    hourlyAds: Object,
  },
};
</script>