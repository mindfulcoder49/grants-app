<template>
  <div class="overlap-analysis mb-8">
    <h2 class="text-xl font-bold mb-4">Overlap Analysis</h2>
    <table class="min-w-full bg-white">
      <thead class="bg-gray-200">
        <tr>
          <th class="py-2">Term</th>
          <th class="py-2">Method A</th>
          <th class="py-2">Time Taken A (ms)</th> <!-- Add Time Taken A column -->
          <th class="py-2">Method B</th>
          <th class="py-2">Time Taken B (ms)</th> <!-- Add Time Taken B column -->
          <th class="py-2">Overlap (%)</th>
          <th class="py-2">Avg. Position Diff</th>
          <th class="py-2">Unique to A</th>
          <th class="py-2">Unique to B</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="overlap in overlaps"
          :key="`${overlap.term}-${overlap.methodA}-${overlap.methodB}`"
          class="text-center"
        >
          <td class="py-2">{{ overlap.term }}</td>
          <td class="py-2">{{ overlap.methodA }}</td>
          <td class="py-2">{{ overlap.timeTakenA.toFixed(2) }} ms</td> <!-- Display Time Taken A -->
          <td class="py-2">{{ overlap.methodB }}</td>
          <td class="py-2">{{ overlap.timeTakenB.toFixed(2) }} ms</td> <!-- Display Time Taken B -->
          <td class="py-2">{{ overlap.overlapPercentage.toFixed(2) }}%</td>
          <td class="py-2">{{ overlap.avgPositionDifference.toFixed(2) }}</td>
          <td class="py-2">
            <button @click="$emit('show-grants', overlap.uniqueToA)">View ({{ overlap.uniqueToA.length }})</button>
          </td>
          <td class="py-2">
            <button @click="$emit('show-grants', overlap.uniqueToB)">View ({{ overlap.uniqueToB.length }})</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>


<script>
export default {
  name: 'OverlapAnalysis',
  props: {
    overlaps: {
      type: Array,
      default: () => [],
    },
  },
};
</script>

<style scoped>
/* Add any necessary styles */
</style>
