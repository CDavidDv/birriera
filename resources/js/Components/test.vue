<script setup>
const img = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFAAAABQCAYAAACOEfKtAAAAAXNSR0IArs4c6QAAAohJREFUeF7t1dunAlEYBfBvIpGSrtJVL5H+//+i10Qv6Sq6kHroIh3fxz7mjHSqZZOsIWZq1jT7N2vvCXq93k24vS0QEPBtOwsSEPMjIOhHQAKiAmCeayABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMO69gZVKRcrlsgRBIMfjUfr9vt1ys9mUQqFg+/v9XobD4UtDuZdPp9PSarUkHo/L9XqV6XQqm83mpeu+erJXQB2QDnS73cpisZButyuHw8E+1WpV5vO5nM/nP+c8M4B8Pn83n0qlLK4PQ/9Xj90De+a675zjFVBvqNPpyG63M0C3r99nMhkZDAZ2z+12W06nk4zHYzvW1haLRZnNZnZcq9VktVrZNXTTB9NoNGS5XNoDcPulUun3vxRZmz+ZTKzhvjbvgOFptV6vDUmBooCuOW6gek42m7Wp7xocRlCger0usVjMIMMPSPfDLfU5jb0C6iDC7dEpfLlcbAo/aqCD0mZGYaMN1QbquqctTyaT39XAaAsciDbivzXQrWEKpuBuejvAXC5n3+n0dGtrIpH4vjUw/LbU9o1GIxv0o7ew/qbTV9+i99ZAt27q8qCbe7t/3VvY18L9Sdf1ugZ+0kB93QsBQVkCEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA4z92vyNfXU0apAAAAABJRU5ErkJggg=='

const menuItems = ref([
    {
      id: 1,
      name: 'Taco',
      description: 'Detalles',
      category: 'Comida',
      price: 15.0,
      quantity: 2,
      image: img
    },
    {
      id: 2,
      name: 'Plato CHICO',
      description: 'Detalles',
      price: 85.0,
      category: 'Paquetes',
      quantity: 2,
      image: img
    },
    {
      id: 3,
      name: 'Plato GRANDE',
      description: 'Detalles',
      price: 110.0,category: 'Paquetes',
      quantity: 0,
      image: img
    },
    {
      id: 4,
      name: 'QUESABIRRIA',
      description: 'Detalles',
      price: 42.0,
      category: 'Comida',
      quantity: 0,
      image: img
    },
    {
      id: 5,
      name: '1/4 Kilo',
      description: 'Detalles',
      price: 130.0,
      quantity: 0,
      category: 'Paquetes',
      image: img
    },
    {
      id: 6,
      name: '1/2 Kilo',
      description: 'Detalles',
      price: 240.0,
      quantity: 0,
      category: 'Comida',
      image: img
    },
    {
      id: 7,
      name: 'Coca Cola',
      description: '500 ml',
      price: 240.0,
      quantity: 1,
      category: 'Bebidas',
      image: img
    },{
      id: 8,
      name: 'Manzanita',
      description: '600 ml',
      price: 240.0,
      quantity: 0,
      category: 'Bebidas',
      image: img
    }
  ])
</script>