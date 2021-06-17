<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Create New Account
      </h2>
    </template>

    <div
      class="
        min-h-screen
        px-8
        py-12
        max-w-md
        mx-auto
        sm:max-w-xl
        bg-gray-100
        p-0
        sm:p-8
      "
    >
      <jet-validation-errors class="mb-4" />

      <form @submit.prevent="submit">
        <div>
          <jet-label for="name" value="Name" />
          <jet-input
            id="name"
            type="text"
            class="mt-1 block w-full"
            v-model="form.name"
            required
            autofocus
            autocomplete="name"
          />
        </div>
        <div class="mt-4">
                <jet-label for="aqlName" value="AQL Name" />
                <jet-input id="aqlName" type="text" class="mt-1 block w-full" v-model="form.aqlName" required />
            </div>
        <div class="mt-4">
          <jet-label for="accountId" value="AccountId" />
          <jet-input
            id="accountId"
            type="number"
            class="mt-1 block w-full"
            v-model="form.accountId"
            required
            autocomplete="accountId"
          />
        </div>
        <div class="mt-4">
          <jet-label for="platform" value="Platform" />
          <select-input
            v-model="form.platform"
            :error="form.errors.platform"
            class="
              mt-1
              block
              w-full
              border-gray-300
              focus:border-indigo-300
              focus:ring focus:ring-indigo-200 focus:ring-opacity-50
              rounded-md
              shadow-sm
            "
          >
            <option :value="null">Select a Platform</option>
            <option value="google">Google</option>
            <option value="yahoo">Yahoo</option>
            <option value="all">All</option>
          </select-input>
        </div>
        <div class="mt-4">
          <jet-label for="dailyBudget" value="Daily Budget" />
          <jet-input
            id="dailyBudget"
            type="number"
            class="mt-1 block w-full"
            v-model="form.dailyBudget"
            required
            autocomplete="Daily Budget"
          />
        </div>

        <div class="mt-4">
          <jet-label for="monthlyBudget" value="Monthly Budget" />
          <jet-input
            id="monthlyBudget"
            type="number"
            class="mt-1 block w-full"
            v-model="form.monthlyBudget"
            required
            autocomplete="Monthly Budget"
          />
        </div>

        <div class="flex items-center justify-end mt-4">
          <jet-button
            class="ml-4"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
          >
            Save
          </jet-button>
        </div>
      </form>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout";

import JetButton from "@/Jetstream/Button";
import JetInput from "@/Jetstream/Input";
import JetLabel from "@/Jetstream/Label";
import JetValidationErrors from "@/Jetstream/ValidationErrors";
import SelectInput from "@/Components/SelectInput";
export default {
  components: {
    AppLayout,
    JetButton,
    JetInput,
    SelectInput,
    JetLabel,
    JetValidationErrors,
  },
  data() {
    return {
      form: this.$inertia.form({
        name: "",
        aqlName: "",
        accountId: "",
        platform: "",
        monthlyBudget: "",
        dailyBudget: "",
        terms: false,
      }),
    };
  },
  methods: {
    submit() {
    //   console.log(this.form);
      this.form.post(this.route("ads-accounts.store"));
    },
  },
};
</script>

<style>
</style>