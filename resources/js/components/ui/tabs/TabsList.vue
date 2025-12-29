<script setup lang="ts">
import type { TabsListProps } from "reka-ui"
import { TabsList as TabsListPrimitive } from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<TabsListProps & { class?: string }>()
</script>

<template>
  <TabsListPrimitive
    :class="cn(
      'inline-flex h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground',
      props.class
    )"
    v-bind="props"
  >
    <slot />
  </TabsListPrimitive>
</template>

