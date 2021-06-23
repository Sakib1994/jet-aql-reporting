<template>
  <aside class="mx-4 w-56 h-screen">
    <div class="uppercase tracking-wide text-c2 mt-6 mb-4 text-center">メニュー</div>
    <div
      class="flex cursor-pointer border px-2 py-1 text-lg text-grey-darkest"
      :class="classObject('全部')"
      @click="$emit('toggleAcoount', '全部')"
    >
      <h2 class="pl-1 text-sm">- 全部</h2>
    </div>
    <div
      v-for="(account, index) in accounts"
      :key="index"
      class="flex cursor-pointer border pl-2 pr-1 py-1 text-lg text-grey-darkest"
      :class="classObject(account)"
      @click="$emit('toggleAcoount', account)"
    >
      <h2 class="pl-1 text-sm">- {{ account }}</h2>
    </div>
  </aside>
</template>

<script>
export default {
    props: {
    accounts: Object,
    selectedAccount: String
  },
  methods: {
    classObject(name) {
      if (name === this.selectedAccount) {
        return "border-l-4 border-red-500";
      }
      return "";
    }
  },
};
</script>

<style>
</style>