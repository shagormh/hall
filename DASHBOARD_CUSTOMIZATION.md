# Dashboard Customization Guide

## Quick Start - Testing Your New Dashboard

### 1. View the Dashboard
Navigate to your dashboard route:
```
http://your-app.test/dashboard
```

### 2. Test Features
- ✅ Check the responsive layout on mobile
- ✅ Click the Dark/Light mode toggle
- ✅ Change the hall selector
- ✅ Hover over cards to see animations
- ✅ Interact with charts (zoom, pan, download)

---

## 🎨 Customization Guide

### Change Primary Color (Green to Another Color)

Replace all `emerald` references with your color:

**In Dashboard.vue:**
```vue
<!-- Change from emerald to indigo -->
FROM: class="text-emerald-300"
TO:   class="text-indigo-300"

FROM: bg-gradient-to-br from-emerald-600/20 to-emerald-900/20
TO:   bg-gradient-to-br from-indigo-600/20 to-indigo-900/20
```

**Color Options:**
- `indigo` - Professional blue
- `green` - Natural green
- `teal` - Modern teal
- `cyan` - Bright cyan
- `amber` - Warm amber
- `violet` - Purple

### Change Secondary Color (Blue)

Replace `blue` and `cyan` references:
```vue
FROM: border-blue-500/50
TO:   border-purple-500/50
```

### Change Dark Background

The dark theme uses `gray-950`. To change:
```vue
FROM: bg-gray-950
TO:   bg-slate-950  <!-- Cooler tone -->
OR
TO:   bg-zinc-950   <!-- Warmer tone -->
```

---

## 🔧 Common Customizations

### 1. Adjust Card Padding

**Current:**
```vue
class="p-6"  <!-- 24px -->
```

**Options:**
```vue
class="p-4"  <!-- 16px - More compact -->
class="p-8"  <!-- 32px - More spacious -->
```

**Apply to:** All `<div class="... p-6 ...">`

### 2. Change Animation Speed

**In Dashboard.vue `<style>`:**
```css
/* Current */
.animate-blob {
    animation: blob 8s infinite;
}

/* Make faster */
animation: blob 4s infinite;

/* Make slower */
animation: blob 12s infinite;
```

### 3. Adjust Card Border Radius

**Current:**
```vue
class="rounded-2xl"  <!-- Large radius -->
```

**Options:**
```vue
class="rounded-lg"   <!-- Medium: 8px -->
class="rounded-xl"   <!-- Large: 12px -->
class="rounded-3xl"  <!-- Extra large: 16px -->
```

### 4. Change Grid Layout

**Current (4 columns):**
```vue
grid-cols-1 md:grid-cols-2 lg:grid-cols-4
```

**Options:**
```vue
<!-- 3 columns -->
grid-cols-1 md:grid-cols-3 lg:grid-cols-3

<!-- 5 columns -->
grid-cols-1 md:grid-cols-2 lg:grid-cols-5
```

### 5. Adjust Chart Heights

**In OccupancyChart.vue:**
```typescript
height="350"  // Current
height="400"  // Taller
height="300"  // Shorter
```

---

## 📝 Typography Customization

### Change Heading Size

**Current:**
```vue
class="text-5xl lg:text-6xl"  <!-- Large -->
```

**Options:**
```vue
class="text-3xl lg:text-4xl"  <!-- Smaller -->
class="text-4xl lg:text-5xl"  <!-- Medium -->
```

### Change Font Weight

```vue
class="font-black"      <!-- 900 (Current) -->
class="font-bold"       <!-- 700 -->
class="font-semibold"   <!-- 600 -->
```

---

## 🎯 Feature Customizations

### Add New Quick Action Button

**In QuickActions.vue:**
```vue
<button
    class="group/btn flex flex-col items-center justify-center p-6 
    bg-gradient-to-br from-teal-600/40 to-teal-600/40 
    hover:from-teal-600/60 hover:to-teal-600/60 
    text-white rounded-xl transition-all duration-300 
    border border-teal-500/30 hover:border-teal-400/60 
    shadow-lg hover:shadow-xl hover:-translate-y-1 backdrop-blur">
    <i class="bi-YOUR-ICON text-3xl mb-3 text-teal-300"></i>
    <span class="text-xs font-black uppercase tracking-wider">Your Action</span>
</button>
```

Replace:
- `teal-600` with your color
- `bi-YOUR-ICON` with Bootstrap icon
- "Your Action" with button text

### Hide/Show Sections

**To hide a section, wrap in v-if:**
```vue
<div v-if="false" class="...">
    <!-- Hidden content -->
</div>
```

**Or use display utility:**
```vue
class="hidden"  <!-- Hide on all screens -->
class="hidden md:block"  <!-- Show only on desktop -->
```

---

## 🌙 Dark Mode Customization

### Change Dark Mode Toggle

**Current location:** Top right of hero section

**Move to another location:**
```vue
<!-- Current -->
<button @click="toggleDarkMode" class="...">

<!-- Move the button elsewhere in the template -->
<button @click="toggleDarkMode" class="...">
```

### Customize Dark Mode Colors

**In tailwind.config.js:**
```javascript
darkMode: 'class',  // Already configured
theme: {
    extend: {
        colors: {
            // Add custom dark colors here
        }
    }
}
```

---

## 📊 Chart Customization

### Change Chart Colors

**In OccupancyChart.vue:**
```typescript
colors: ['#06b6d4', '#8b5cf6'],  // Cyan, Purple

// Change to:
colors: ['#10b981', '#f59e0b'],  // Green, Amber
```

### Add Chart Title

```typescript
chartOptions = computed(() => ({
    chart: {
        title: {
            text: "Hall Occupancy Analysis"
        }
    }
}));
```

### Change Chart Type

```typescript
type="bar"    // Current
type="area"   // Area chart
type="line"   // Line chart
type="pie"    // Pie chart
type="radar"  // Radar chart
```

---

## 🔄 State Management

### Persist Additional Data

**Add to localStorage:**
```typescript
watch(someValue, (newValue) => {
    localStorage.setItem('key', JSON.stringify(newValue));
});

// Retrieve on mount
onMounted(() => {
    const saved = localStorage.getItem('key');
    if (saved) {
        someValue.value = JSON.parse(saved);
    }
});
```

### Add New Computed Values

```typescript
const customComputed = computed(() => {
    // Your logic here
    return calculation;
});
```

---

## 🚀 Performance Optimizations

### Lazy Load Charts

```vue
<Suspense>
    <template #default>
        <OccupancyChart :data="hallOccupancy" />
    </template>
    <template #fallback>
        <div>Loading chart...</div>
    </template>
</Suspense>
```

### Debounce Hall Selection

```typescript
import { debounce } from 'lodash-es';

const filterByHall = debounce(() => {
    router.get(route('dashboard'), { hall_id: currentHallId.value }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);
```

### Cache Chart Data

```typescript
const chartCache = new Map();

const getCachedChart = (id) => {
    if (chartCache.has(id)) {
        return chartCache.get(id);
    }
    // Fetch and cache
    const data = fetchChart(id);
    chartCache.set(id, data);
    return data;
};
```

---

## 🐛 Troubleshooting

### Icons Not Showing
- Ensure Bootstrap Icons are loaded in main.ts/app.js
- Check icon class names: `bi-icon-name`
- Use [Bootstrap Icons](https://icons.getbootstrap.com/) reference

### Colors Not Applying
- Check Tailwind CSS is configured
- Clear browser cache (Ctrl+Shift+Delete)
- Rebuild CSS: `npm run build`

### Charts Not Rendering
- Check ApexCharts is installed: `npm install apexcharts`
- Verify chart data format
- Check browser console for errors

### Dark Mode Not Working
- Check localStorage is enabled
- Verify `<html>` tag gets `dark` class
- Clear localStorage: `localStorage.clear()`

### Animations Laggy
- Check GPU acceleration: use `transform` not `left/top`
- Reduce blur effects on lower-end devices
- Test in Chrome DevTools Performance tab

---

## 📱 Mobile Optimization Tips

### Test on Real Devices
```bash
# Get your local IP
ipconfig getifaddr en0  # Mac
ipconfig              # Windows

# Access from mobile
http://YOUR_IP:8000
```

### Adjust for Touch
```vue
<!-- Increase touch target size -->
class="p-6"  <!-- 24px padding -->
class="h-14"  <!-- 56px height minimum -->

<!-- Increase spacing between elements -->
gap-6  <!-- Instead of gap-4 -->
```

### Reduce Animations
```css
@media (prefers-reduced-motion: reduce) {
    * {
        animation: none !important;
        transition: none !important;
    }
}
```

---

## 🎓 Learning Resources

### Tailwind CSS
- [Tailwind Docs](https://tailwindcss.com/docs)
- [Color Reference](https://tailwindcss.com/docs/customizing-colors)
- [Responsive Design](https://tailwindcss.com/docs/responsive-design)

### Vue 3
- [Vue 3 Guide](https://vuejs.org/guide/)
- [Composition API](https://vuejs.org/guide/extras/composition-api-faq.html)
- [Template Syntax](https://vuejs.org/guide/extras/template-syntax.html)

### ApexCharts
- [ApexCharts Docs](https://apexcharts.com/docs/)
- [Chart Types](https://apexcharts.com/docs/chart-types/)
- [Configurations](https://apexcharts.com/docs/options/)

---

## 💡 Best Practices

1. **Always use consistent colors** - Don't mix emerald with indigo randomly
2. **Test responsive design** - Use browser DevTools
3. **Optimize images** - Use WebP format for better performance
4. **Minimize API calls** - Cache data when possible
5. **Keep animations subtle** - Avoid overwhelming users
6. **Maintain accessibility** - Test with keyboard navigation
7. **Document changes** - Keep this file updated
8. **Version control** - Commit customizations

---

## 🔗 File Structure Reference

```
resources/
├── js/
│   ├── Pages/
│   │   └── Dashboard.vue          ← Main dashboard page
│   └── Components/
│       └── Dashboard/
│           ├── StatCard.vue       ← Stats cards
│           ├── OccupancyChart.vue ← Bar chart
│           ├── TrendsChart.vue    ← Line chart
│           ├── QuickActions.vue   ← Action buttons
│           ├── PendingAlerts.vue  ← Alert cards
│           └── RecentActivities.vue ← Activity timeline
└── css/
    └── app.css                    ← Global styles
```

---

## 📞 Need Help?

Check these locations first:
1. Component files for specific styling
2. Tailwind documentation for CSS classes
3. Bootstrap Icons for icon names
4. Vue documentation for JavaScript issues
5. This file for common customizations

---

**Happy Customizing!** 🎨✨

Last Updated: January 2026
