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

      <form @submit.prevent="form.patch(route('hourly-ads.update', this.form))">
        <div>
          <jet-label for="impressions" value="表示回数" />
          <jet-input
            id="impressions"
            type="text"
            class="mt-1 block w-full"
            v-model="form.impressions"
            required
            autofocus
            autocomplete="name"
          />
        </div>

        <div class="mt-4">
          <jet-label for="clicks" value="クリック数" />
          <jet-input
            id="clicks"
            type="number"
            class="mt-1 block w-full"
            v-model="form.clicks"
            required
            autocomplete="accountId"
          />
        </div>
        <div class="mt-4">
          <jet-label for="ctr" value="クリック率" />
          <jet-input
            id="ctr"
            type="text"
            class="mt-1 block w-full"
            v-model="form.ctr"
            required
            autocomplete="Cost percentige"
          />
        </div>

        <div class="mt-4">
          <jet-label for="cpc" value="クリック単価" />
          <jet-input
            id="cpc"
            type="text"
            class="mt-1 block w-full"
            v-model="form.cpc"
            required
            autocomplete="Cost per click"
          />
        </div>
        <div class="mt-4">
          <jet-label for="cost" value="費用" />
          <jet-input
            id="cost"
            type="text"
            class="mt-1 block w-full"
            v-model="form.cost"
            required
            autocomplete="Cost"
          />
        </div>

        <div class="mt-4">
          <jet-label for="conversions" value="コンバージョン" />
          <jet-input
            id="conversions"
            type="number"
            class="mt-1 block w-full"
            v-model="form.conversions"
            required
            autocomplete="Conversions"
          />
        </div>
        <div class="mt-4">
          <jet-label for="conversions_rate" value="コンバージョン率" />
          <jet-input
            id="conversions_rate"
            type="text"
            class="mt-1 block w-full"
            v-model="form.conversions_rate"
            required
            autocomplete="Conversions_rate"
          />
        </div>

        <div class="mt-4">
          <jet-label for="cost_per_conversion" value="コンバージョン単価" />
          <jet-input
            id="cost_per_conversion"
            type="text"
            class="mt-1 block w-full"
            v-model="form.cost_per_conversion"
            required
            autocomplete="Cost per conversion"
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
    hourlyAdsData: Object,
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
        id: props.hourlyAdsData.id,
        clicks: props.hourlyAdsData.clicks,
        impressions: props.hourlyAdsData.impressions,
        ctr: props.hourlyAdsData.ctr,
        cost: props.hourlyAdsData.cost,
        cpc: props.hourlyAdsData.cpc,
        conversions: props.hourlyAdsData.conversions,
        conversions_rate: props.hourlyAdsData.conversions_rate,
        cost_per_conversion: props.hourlyAdsData.cost_per_conversion,
      });
      return { form };
  },
};
</script>