/* Tailwind Play CDN config — shared across every mockup.
   Loaded synchronously right after the CDN <script>, so config
   is set before Tailwind's first DOM scan. Colors mirror the
   OKLCH tokens in app.css, derived from the real brand. */
tailwind.config = {
  theme: {
    extend: {
      fontFamily: {
        display: ['Fredoka', 'system-ui', 'sans-serif'],
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      fontWeight: { 400:'400', 500:'500', 600:'600', 700:'700' },
      spacing: { 13:'3.25rem', 15:'3.75rem', 18:'4.5rem' },
      screens: { xs:'400px' },
      colors: {
        azure: {
          50:'oklch(0.965 0.022 256)',100:'oklch(0.930 0.045 256)',200:'oklch(0.872 0.085 256)',
          300:'oklch(0.788 0.130 256)',400:'oklch(0.702 0.176 256)',500:'oklch(0.615 0.211 256)',
          600:'oklch(0.545 0.205 257)',700:'oklch(0.470 0.176 256)',800:'oklch(0.400 0.140 252)',
          900:'oklch(0.340 0.105 250)',950:'oklch(0.262 0.078 252)',
        },
        navy: {
          50:'oklch(0.955 0.013 246)',100:'oklch(0.908 0.025 246)',200:'oklch(0.820 0.044 247)',
          300:'oklch(0.708 0.060 247)',400:'oklch(0.560 0.080 248)',500:'oklch(0.470 0.092 248)',
          600:'oklch(0.405 0.094 248)',700:'oklch(0.345 0.082 249)',800:'oklch(0.288 0.066 250)',
          900:'oklch(0.232 0.052 251)',950:'oklch(0.182 0.040 252)',
        },
        lagon: {
          300:'oklch(0.852 0.090 202)',400:'oklch(0.788 0.110 204)',500:'oklch(0.720 0.113 207)',
          600:'oklch(0.620 0.100 209)',700:'oklch(0.520 0.085 211)',
        },
        sun: { 300:'oklch(0.890 0.085 85)',400:'oklch(0.825 0.130 80)',500:'oklch(0.760 0.150 72)' },
        sand: { 50:'oklch(0.987 0.005 85)',100:'oklch(0.967 0.008 84)',200:'oklch(0.928 0.011 80)',300:'oklch(0.875 0.013 75)' },
        ink: { 950:'oklch(0.255 0.045 250)',900:'oklch(0.310 0.045 250)',700:'oklch(0.445 0.030 250)',500:'oklch(0.585 0.024 250)',400:'oklch(0.690 0.018 250)' },
        success:'oklch(0.700 0.150 155)', warn:'oklch(0.800 0.130 80)', danger:'oklch(0.620 0.210 25)',
      },
      boxShadow: {
        xs:'0 1px 2px oklch(0.29 0.07 250 / 0.06)',
        sm:'0 1px 2px oklch(0.29 0.07 250 / 0.05), 0 4px 12px -6px oklch(0.29 0.07 250 / 0.10)',
        md:'0 2px 4px oklch(0.29 0.07 250 / 0.05), 0 14px 30px -10px oklch(0.29 0.07 250 / 0.16)',
        lg:'0 4px 8px oklch(0.29 0.07 250 / 0.06), 0 30px 60px -16px oklch(0.29 0.07 250 / 0.24)',
      },
      borderRadius: { xl:'0.875rem','2xl':'1.25rem','3xl':'1.75rem' },
      maxWidth: { content:'75rem' },
      transitionTimingFunction: { 'out-quint':'cubic-bezier(0.22, 1, 0.36, 1)' },
    },
  },
};
