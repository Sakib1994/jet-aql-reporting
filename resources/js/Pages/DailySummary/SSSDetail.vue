<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        SSS Hourly Report
      </h2>
    </template>
    <div class="py-8">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div
          class="pt-2 sm:px-15 bg-white overflow-hidden shadow-sm sm:rounded-lg"
        >
          <div class="flex w-full h-12 space-x-2 justify-end">
            <!-- <JsonCSV :data="json_data"> Download Data </JsonCSV> -->
            <select v-model="platform" @change="onChange($event)">
              <option disabled value="">Select Platform</option>
              <option>all</option>
              <option>google</option>
              <option>yahoo</option>
            </select>
            <button
              class="inline-block bg-green-500 hover:bg-green-400 focus:outline-none focus:ring focus:ring-offset-2 focus:ring-green-500 focus:ring-opacity-50 text-white p-3 hover:-translate-y-0.5 transform transition active:bg-green-600 rounded-lg shadow-lg uppercase tracking-wider font-semibold text-sm"
              @click="csvExport(accountList)"
            >
              Download CSV
            </button>
          </div>
          <div class="flex">
            <side-bar
              :accounts="accounts"
              :selectedAccount="selectedAccount"
              @toggleAcoount="toggleAcoount"
            />
            <div
              class="w-full h-screen py-6 px-2 bg-white border-b border-gray-200"
            >
              <table class="table-auto mx-auto">
                <thead>
                  <tr>
                    <th
                      v-for="(header, index) in table_header"
                      :key="index"
                      class="border mx-1 border-r-1 border-red-100 bg-green-600 text-white"
                    >
                      <span class="text-xs font-light" v-html="header"></span>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(account, index) in displayedDatas"
                    :key="index"
                    class="hover:bg-gray-200"
                    :class="[index % 2 != 0 ? 'bg-green-100' : '']"
                  >
                    <td class="px-1">
                      <span
                        class="text-sm font-light"
                        v-html="account.日付"
                      ></span>
                    </td>
                    <td class="px-1">
                      <span
                        class="text-sm font-light"
                        v-html="account.入電数"
                      ></span>
                    </td>
                    <td class="px-1">&yen; 
                      <span
                        class="text-sm font-light"
                        v-html="account.入電単価"
                      ></span>
                    </td>
                    <td class="px-1">
                      <span
                        class="text-sm font-light"
                        v-html="account.表示回数"
                      ></span>
                    </td>
                    <td class="px-1">
                      <span
                        class="text-sm font-light"
                        v-html="account.クリック数"
                      ></span>
                    </td>
                    <td class="px-1">
                      <span
                        class="text-sm font-light"
                        v-html="account.クリック率"
                      ></span>
                    </td>
                    <td class="px-1">&yen; 
                      <span
                        class="text-sm font-light"
                        v-html="account.クリック単価"
                      ></span>
                    </td>
                    <td class="px-1">&yen; 
                      <span
                        class="text-sm font-light"
                        v-html="account.費用"
                      ></span>
                    </td>
                    <td class="px-1">
                      <span
                        class="text-sm font-light"
                        v-html="account.コンバージョン"
                      ></span>
                    </td>
                    <td class="px-1">
                      <span
                        class="text-sm font-light"
                        v-html="account.コンバージョン率"
                      ></span>
                    </td>
                    <td class="px-1">&yen; 
                      <span
                        class="text-sm font-light"
                        v-html="account.コンバージョン単価"
                      ></span>
                    </td>
                  </tr>
                </tbody>
              </table>
              <ul
                v-if="pages.length > 1"
                class="flex pl-0 list-none rounded my-2"
              >
                <li
                  v-if="page != 1"
                  @click="page--"
                  class="relative block py-2 px-3 leading-tight bg-white border border-gray-300 text-blue-700 border-r-0 ml-0 rounded-l hover:bg-gray-200"
                >
                  <a class="page-link" href="#">Previous</a>
                </li>
                <li
                  :key="pageNumber"
                  v-for="pageNumber in pages.slice(page - 1, page + 5)"
                  @click="page = pageNumber"
                  class="relative block py-2 px-3 leading-tight bg-white border border-gray-300 text-blue-700 border-r-0 hover:bg-gray-200"
                >
                  <a class="page-link" href="#">{{ pageNumber }}</a>
                </li>
                <li
                  @click="page++"
                  v-if="page < pages.length"
                  class="relative block py-2 px-3 leading-tight bg-white border border-gray-300 text-blue-700 rounded-r hover:bg-gray-200"
                >
                  <a class="page-link" href="#">Next</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";
import SideBar from "@/Components/Sidebar";

export default {
  components: {
    AppLayout,
    SideBar,
  },
  props: {
    accounts: Object,
    table_header: Array,
    hourlyDatas: Array,
  },
  data: () => ({
    selectedAccount: "合計",
    accountList: [],
    platform: "all",
    page: 1,
    perPage: 12,
    pages: [],
  }),
  methods: {
    csvExport(arrData) {
      let csvContent = "data:text/csv;charset=utf-8,";
      csvContent += [
        Object.keys(arrData[0]).join(","),
        ...arrData.map((item) => Object.values(item).join(",")),
      ]
        .join("\r\n")
        .replace(/(^\[)|(\]$)/gm, "");

      const data = encodeURI(csvContent);
      const link = document.createElement("a");
      link.setAttribute("href", data);
      link.setAttribute("download", this.selectedAccount + " export.csv");
      link.click();
    },
    onChange(event) {
      console.log(event.target.value);
      let data = route("daily-summary.sss-detail", {
        date: this.table_header[0],
        adsPlatform: event.target.value,
      });
      if (event.target.value == "all") {
        data = route("daily-summary.sss-detail", {
          date: this.table_header[0],
        });
      }
      const link = document.createElement("a");
      link.setAttribute("href", data);
      link.click();
    },
    toggleAcoount(name) {
      console.log(name);
      // this.displayedDatas = [];
      this.selectedAccount = name;
      const accs = [];
      this.hourlyDatas.forEach((element) => {
        element.forEach((elementinternal) => {
          if (elementinternal.accountName == this.selectedAccount) {
            const hourlysummary = {
              日付: elementinternal.time,
              入電数: elementinternal.入電数,
              入電単価: elementinternal.入電単価.toFixed(2),
              表示回数: elementinternal.表示回数,
              クリック数: elementinternal.クリック数,
              クリック率: elementinternal.クリック率
                ? elementinternal.クリック率.toFixed(2) + "%"
                : "0%",
              クリック単価: elementinternal.クリック単価
                ? elementinternal.クリック単価.toFixed(2)
                : 0,
              費用: elementinternal.費用
                ? elementinternal.費用.toFixed(2)
                : 0,
              コンバージョン: elementinternal.コンバージョン,
              コンバージョン率: elementinternal.コンバージョン率
                ? elementinternal.コンバージョン率.toFixed(2) + "%"
                : "0%",
              コンバージョン単価: elementinternal.コンバージョン単価
                ? elementinternal.コンバージョン単価.toFixed(2)
                : 0,
            };
            accs.push(hourlysummary);
          }
        });
      });
      this.accountList = accs;
    },
    setPages() {
      let numberOfPages = Math.ceil(this.accountList.length / this.perPage);
      if (this.pages.length == numberOfPages) {
        return 0;
      }
      for (let index = 1; index <= numberOfPages; index++) {
        this.pages.push(index);
      }
    },
    paginate(accountList) {
      let page = this.page;
      let perPage = this.perPage;
      let from = page * perPage - perPage;
      let to = page * perPage;
      return accountList.slice(from, to);
    },
  },
  computed: {
    displayedDatas() {
      return this.paginate(this.accountList);
    },
  },
  watch: {
    accountList() {
      this.setPages();
    },
  },
  mounted() {
    // console.log(route().current('daily-summary.sss-detail', { adsPlatform: 'google' }));
    if (
      route().current("daily-summary.sss-detail", { adsPlatform: "google" })
    ) {
      this.platform = "google";
    } else if (
      route().current("daily-summary.sss-detail", { adsPlatform: "yahoo" })
    ) {
      this.platform = "yahoo";
    }
    const accs = [];
    this.hourlyDatas.forEach((element) => {
      element.forEach((elementinternal) => {
        if (elementinternal.accountName == this.selectedAccount) {
          const hourlysummary = {
            日付: elementinternal.time,
            入電数: elementinternal.入電数,
            入電単価: elementinternal.入電単価.toFixed(2),
            表示回数: elementinternal.表示回数,
            クリック数: elementinternal.クリック数,
            クリック率: elementinternal.クリック率
              ? elementinternal.クリック率.toFixed(2) + "%"
              : "0%",
            クリック単価: elementinternal.クリック単価
              ? elementinternal.クリック単価.toFixed(2)
              : 0,
            費用: elementinternal.費用
              ? elementinternal.費用.toFixed(2)
              : 0,
            コンバージョン: elementinternal.コンバージョン,
            コンバージョン率: elementinternal.コンバージョン率
              ? elementinternal.コンバージョン率.toFixed(2) + "%"
              : "0%",
            コンバージョン単価: elementinternal.コンバージョン単価
              ? elementinternal.コンバージョン単価.toFixed(2)
              : 0,
          };
          // console.log(hourlysummary);
          accs.push(hourlysummary);
        }
      });
    });
    this.accountList = accs;
  },
};
</script>
