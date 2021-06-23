<template>
  <app-layout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Edit Hourly Ads Account Info
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

      <form @submit.prevent="form.patch(route('daily-summary.update', this.form))">
        <div>
          <jet-label for="googleAds" value="Google Ads" />
          <jet-input
            id="googleAds"
            type="text"
            class="mt-1 block w-full"
            v-model="form.googleAds"
            required
            autofocus
            autocomplete="Google Ads"
          />
        </div>

        <div class="mt-4">
          <jet-label for="yahooAds" value="Yahoo Ads" />
          <jet-input
            id="yahooAds"
            type="number"
            class="mt-1 block w-full"
            v-model="form.yahooAds"
            required
            autocomplete="Yahoo Ads"
          />
        </div>
        <div class="mt-4">
          <jet-label for="total" value="total" />
          <jet-input
            id="total"
            type="text"
            class="mt-1 block w-full"
            v-model="form.total"
            required
            autocomplete="Total"
          />
        </div>

        <div class="mt-4">
          <jet-label for="budget" value="Budget" />
          <jet-input
            id="budget"
            type="text"
            class="mt-1 block w-full"
            v-model="form.budget"
            required
            autocomplete="Budget"
          />
        </div>
        <div class="mt-4">
          <jet-label for="numberOfCalls" value="Number Of Calls" />
          <jet-input
            id="numberOfCalls"
            type="text"
            class="mt-1 block w-full"
            v-model="form.numberOfCalls"
            required
            autocomplete="Number Of Calls"
          />
        </div>

        <div class="mt-4">
          <jet-label for="costPerCall" value="Cost Per Call" />
          <jet-input
            id="costPerCall"
            type="number"
            class="mt-1 block w-full"
            v-model="form.costPerCall"
            required
            autocomplete="Cost Per Call"
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
import { useForm } from "@inertiajs/inertia-vue3";
export default {
  props: {
    accountsSummarize: Object,
  },
  components: {
    AppLayout,
    JetButton,
    JetInput,
    SelectInput,
    JetLabel,
    JetValidationErrors,
  },
  setup(props){
      const form = useForm({
        id: props.accountsSummarize.id,
        googleAds: props.accountsSummarize.googleAds,
        yahooAds: props.accountsSummarize.yahooAds,
        total: props.accountsSummarize.total,
        budget: props.accountsSummarize.budget,
        numberOfCalls: props.accountsSummarize.numberOfCalls,
        costPerCall: props.accountsSummarize.costPerCall,
      });
      return { form };
  },
};
</script>