<template>
    <div class="chart-card">
        <div class="chart-card-head">
            <div>
                <h3 class="chart-title"><i class="ti" :class="icon"></i> {{ title }}</h3>
                <p class="chart-total">{{ total }} <span>sur la période</span></p>
            </div>
        </div>

        <div class="chart-canvas">
            <svg :viewBox="`0 0 ${width} ${height}`" class="chart-svg" preserveAspectRatio="none">
                <!-- Lignes de repère horizontales -->
                <line v-for="n in 3" :key="n" x1="0" :x2="width" :y1="(height - padding) * (n / 4)" :y2="(height - padding) * (n / 4)" class="chart-gridline" />

                <!-- Aire sous la courbe -->
                <polygon :points="areaPoints" class="chart-area" :style="{ fill: color }" />

                <!-- Courbe -->
                <polyline :points="linePoints" class="chart-line" :style="{ stroke: color }" />

                <!-- Points visibles (seulement si peu de données, sinon ça surcharge) -->
                <template v-if="series.length <= 31">
                    <circle v-for="(p, i) in points" :key="'dot-' + i" :cx="p.x" :cy="p.y" r="2.5" class="chart-dot" :style="{ fill: color }" />
                </template>

                <!-- Zones cliquables invisibles, plus larges — toujours présentes, même
                     quand les points visibles sont masqués sur les périodes denses (90j) -->
                <circle
                    v-for="(p, i) in points" :key="'hit-' + i"
                    :cx="p.x" :cy="p.y" r="10" class="chart-hit"
                    :class="{ 'chart-hit--active': activeIndex === i }"
                    @click="selectPoint(i)"
                    @mouseenter="activeIndex = i"
                    @mouseleave="activeIndex = null"
                />
            </svg>

            <div v-if="activeIndex !== null" class="chart-tooltip" :style="tooltipStyle">
                <strong>{{ series[activeIndex].count }}</strong> le {{ formatDate(series[activeIndex].date, true) }}
                <span class="chart-tooltip-cta">Voir le détail</span>
            </div>
        </div>

        <div class="chart-labels">
            <span>{{ formatDate(series[0]?.date) }}</span>
            <span>{{ formatDate(series[series.length - 1]?.date) }}</span>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: { type: String, required: true },
        icon: { type: String, default: 'ti-chart-line' },
        color: { type: String, default: '#1D9E75' },
        series: { type: Array, required: true }, // [{ date: 'YYYY-MM-DD', count: number }, ...]
    },
    emits: ['point-click'],
    data() {
        return {
            width: 600,
            height: 160,
            padding: 24,
            activeIndex: null,
        };
    },
    computed: {
        total() {
            return this.series.reduce((sum, p) => sum + p.count, 0);
        },
        maxValue() {
            return Math.max(1, ...this.series.map((p) => p.count));
        },
        points() {
            const n = this.series.length;
            if (n === 0) return [];
            const usableHeight = this.height - this.padding;
            return this.series.map((p, i) => ({
                x: n === 1 ? this.width / 2 : (i / (n - 1)) * this.width,
                y: usableHeight - (p.count / this.maxValue) * usableHeight,
            }));
        },
        linePoints() {
            return this.points.map((p) => `${p.x},${p.y}`).join(' ');
        },
        areaPoints() {
            if (this.points.length === 0) return '';
            const base = this.height - this.padding;
            return `0,${base} ` + this.points.map((p) => `${p.x},${p.y}`).join(' ') + ` ${this.width},${base}`;
        },
        tooltipStyle() {
            if (this.activeIndex === null) return {};
            const p = this.points[this.activeIndex];
            return {
                left: `${(p.x / this.width) * 100}%`,
                top: `${(p.y / this.height) * 100}%`,
            };
        },
    },
    methods: {
        formatDate(dateStr, long = false) {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleDateString('fr-CH', long
                ? { day: 'numeric', month: 'long' }
                : { day: 'numeric', month: 'short' });
        },
        selectPoint(i) {
            this.activeIndex = i;
            this.$emit('point-click', this.series[i]);
        },
    },
};
</script>

<style scoped>
.chart-card { background: #fff; border: 0.5px solid #E7E9E7; border-radius: 16px; padding: 20px; }
.chart-card-head { margin-bottom: 12px; }
.chart-title { display: flex; align-items: center; gap: 7px; font-size: 13.5px; font-weight: 500; color: #4B5A54; margin: 0; }
.chart-title i { color: #1D9E75; font-size: 16px; }
.chart-total { font-size: 24px; font-weight: 600; color: #10241D; margin: 4px 0 0; }
.chart-total span { font-size: 12px; font-weight: 400; color: #8FA098; }

.chart-svg { width: 100%; height: 160px; display: block; overflow: visible; }
.chart-gridline { stroke: #F0F1F0; stroke-width: 1; }
.chart-area { opacity: 0.08; }
.chart-line { fill: none; stroke-width: 2; stroke-linejoin: round; stroke-linecap: round; }
.chart-dot { opacity: 0.9; }

.chart-canvas { position: relative; }
.chart-hit { fill: transparent; cursor: pointer; }
.chart-hit--active { fill: rgba(16, 36, 29, 0.06); }

.chart-tooltip {
    position: absolute; transform: translate(-50%, -130%); background: #10241D; color: #fff;
    font-size: 11.5px; padding: 6px 10px; border-radius: 8px; white-space: nowrap; pointer-events: none;
    display: flex; flex-direction: column; align-items: center; gap: 1px; z-index: 5;
}
.chart-tooltip strong { font-size: 12.5px; }
.chart-tooltip-cta { font-size: 10px; color: rgba(255,255,255,.6); }

.chart-labels { display: flex; justify-content: space-between; font-size: 11px; color: #8FA098; margin-top: 6px; }
</style>
