<template>
  <div class="container mx-auto p-8 md:p-10">
    <h1 class="text-2xl font-bold mb-4">Gestión de Inventario</h1>

    <div class="flex flex-col md:flex-row gap-10 w-full">
      <div class="md:w-4/12 w-full">
        <!-- Formulario para agregar/editar item -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
          <h2 class="text-xl font-semibold mb-4">{{ editingItem ? 'Editar Item' : 'Agregar Nuevo Item' }}</h2>
          <form @submit.prevent="saveItem" class="space-y-4">
            <!--imagen-->
            <div class=" flex justify-center">
              <img v-if="currentItem.imagen" :src="currentItem.imagen"  alt="Imagen del item"
                  class="mt-4 max-w-xs rounded border border-gray-300 hover:cursor-pointer size-32" 
                  @click="openModal = true" 
                  @error="event => event.target.src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABHsAAAPqCAMAAAA0EqO0AAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAKCUExURfLy8vLy8s3MzKilpZqXl46KioF9fYyIiJiUlKShocfFxezs7OLi4qekpHt3d3ZxcZ2amtnY2LGurunp6ebl5ZeTk4mFhdjX19zb24aCgnl0dMzKyo2JiX14eN3c3J6amu7u7sXDw7OwsIqGhnhzc+Xk5MLAwK6srIqFhe/v79va2tXU1MC+vsjHx7Sxsby6uqikpO3t7eHg4NTT08jGxry5ua+srKOfn5aSkouHh6OgoNLR0Z+cnOrq6qGdnX55eYF8fKmmpuTj4+Lh4b27u5iVlXdycrKvr97d3bWyspCMjJWRkefn56ypqYSAgNbV1fHx8bazs3p2doiDg7+9vYR/f6qoqOno6JqWlra0tJKOjsfGxuPj47e0tKajo7q4uK6rq9HQ0H97e9HPz9rZ2cTDw62rq5eUlMnHx+Hh4aqnp4qHh5GNjYJ9fbOxscbExOrp6ZuXl9fW1ujo6ImEhJmWlri2tn15eezr67u5ubm3t6Kfn62qqqCdnYyHh4+Li4eDg8LBwbCurqejo6uoqM/NzZSQkKSgoH96etDOzsPBwcnIyMvJyb68vLSysouGhrCtrZOPj3p1dYB7e42IiJmVlaWioufm5szLy4N+frq3t97e3sTCwnt2dpWSktDPz5+bm8XExOPi4oWBgayqqpyYmOvq6vHw8KKensG/v9LQ0LKwsJOQkMrIyIaBgbe1tXx4eODf34N/f5yZmaGenuvr66mnp+Tk5J2Zmc/Ozp6bm7m2tq+trdTS0nh0dLGvr9fV1YeCgoiEhKupqZSRkbu4uO3s7JuYmOjn59/e3tvb287NzfDw8IJ+fsvKynx3d8rJyb26us7MzIWAgLi1tYB8fG2VGR8AAAABdFJOU/4a4wd9AAAACXBIWXMAADLAAAAywAEoZFrbAAAgjklEQVR4Xu3d+dttdXnfcbcTBcGDEJkEIzK0iEDhIIJhEDkcBbGAcgRFEA1JxaJGjDFOJRqaGoytksRo1JhYY0pb56RNTdLEpKlpm9ZO+X+KcCOcc/bez7DX3p91L16vX5Rn32s4+7q+7+t59rDW0542A9i0p2kPEKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYACdoDJGgPkKA9QIL2AAnaAyRoD5CgPUCC9gAJ2gMkaA+QoD1AgvYwdk9/xjOf9ewj/t6RRz2nfsAkaA+jdvQxz93zY8c+r37KBGgPY3ZcVedxxxxfD9Ce9jBeP/H8Ss4TTjixHqM77WG0Tjq5gvNkp7ygHqU57WGsnnNq5eZgp72wHqc37WGsfrJic6hnvagGaE17GKnTKzWHe3FN0Jr2MFJnVGkOd+ZZNUJn2sM4nV2hmefv1wydaQ/jdGx1Zp5/UDN0pj2M0jmnVGfmeklN0Zj2MErnVmXme2lN0Zj2MErnVWXmO7+maEx7GKULqjLz/cOaojHtmZALL9p78csuefmll23c819xyU/VSQzk/KrMfJfXFI1pz0RccfqVV9XCDHnl1a+qcxmC9kye9kzBNfuurUUZtf/VdT4D0J7J0572XnPd9bUi81472K8+2jN52tPcDa+r5TgO/+jGOq9Vac/kaU9rN938+lqNY/GGG+rUVqQ9k6c9nd1yoNbiiFxb57Yi7Zk87enrvPnX1kq7oE5vNdozedrT1htrHY7Nswe5tJf2TJ72NHXrbbUMx+dNdYor0Z7J056eTjyhVuEIvbnOcSXaM3na09LtZ9YiHKW31FmuQnsmT3s6uqOW4EgdVae5Cu2ZPO1p6M5agWP11jrPVWjP5GlPPyP/rWfPnrvqRFehPZOnPe3cXutvvI6pM12F9kye9nRz4qhfZn7UEFcV1J7J055mbh3xm+uPG+JqytozedrTzHg/UviEt9W5rkJ7Jk97ehnrFyme7O11rivRnsnTnlaW371hJI6sk12J9kye9rQyzm+uH+Kn62RXoj2Tpz2d3FJLb9SG6YL2TJ72NHLTCC8Vdri762xXoz2Tpz2N3Fwrb9QGul2x9kye9vRxw9iuzTzPJXWyq9KeydOePsZ1R4r5BrtRuvZMnva08ZpadyP23J+pc12d9kye9rRxXa278frZW+tUB6A9k6c9bYzn7qNz/eMLLqoTHYT2TJ72dHFNLbstHDjyuH3v2Li9F11Yp7k995z9pndevtQ/qX/PfAdqaoFjzn/pS+pIjJb2dLGvlt1S955b02N2zkZuIH/GJWfV8Rgn7eni2lpTS9x5T82O2r531emu25nvHuROYayJ9jRxRS2oxfa/p0ZH7efeW6e7Cc96YR2VEdKeJk6v5bTQKffV5Kg974w63c047QV1XMZHe5q4slbTIvtbpOd9P1+nuymnnFhHZnS0p4mrajEt0uIPrtn762w354Tj69CMjfb0cGEtpUXurLlx+4U6200a4qYZrIP29HBRraRFWrzDdcUH6mw3yl9dI6U9PeythbTAvTU2bpfU2W7WsXV0RkZ7eri4FtICHT5SOJuFbu/zi3V4xkV7evhgraP5DtTUuL2kznbTjqrjMy7a08PyP1cGuTPE2n2oznbTejw7Tz3a08OHax3Nd1xNjdtH6mw37do6PuOiPT1cWutovn01NW4frbPdtH9ax2dctKeHy2odzfeOmhq35f1cn2fX8RkX7elhCu1Z/nfj+jyrjs+4aE8PU2hP5uM9e/Y8s47PuGhPD1NozwV1tpv2jDo+46I9PUyhPVt9L2Rdnl7HZ1y0p4cptGd2f53uZj23js7IaE8Pk2hP5sOFvsg+UtrTwyTaM/ulOt+NOroOzshoTw/TaM/H6nw36eN1bMZGe3qYRntmv1wnvDnPryMzOtrTw0Tas/HPNp/8E3VgRkd7ephKe45/Zp3yZpx6Uh2X8dGeHqbSntnsgTrnTfjJ59RBGSHt6WE67Zn9s1+ps163M06vIzJK2tPDhNozm138zz9RJ74+pxx7dh2NkdKeHjbSnltP+tUH79j3yRuPPqd+sD73/dqn/sVSr61/2nz/8vylLjjv3PX/E1iR9vSw5vbs/fTlnzn5odrbI379N37zs2f/Vj0YcX6dyXyX1xSNaU8Pa2zPTZ875vO1n0O89re/UDObpz2Tpz09rKs9t37x8i/VTuY69dU31uSGac/kaU8P62nPWe+sHSxzROQeM9ozedrTwzra86IXn1nbb+F3vlxbbJD2TJ729LCG9lzwytp6G3737tpoY7Rn8rSnh8Hb83tn1Lbb9Iyv1IYboj2Tpz09DNyes3bxnc7N3gRMeyZPe3oYtj17/1VtuCNfra03QnsmT3t6GLQ9D9ZmO/X7X6sdbID2TJ729DBke15cW+3cZZt7yVl7Jk97ehiuPX/wr2uj3Xhox68s7Zb2TJ729DBYey66t7bZpV+o/ayb9kye9vQwVHv+YMX07NnzcO1pzbRn8rSnh6Has8ofXOXf1K7WS3smT3t6GKg9/7Y2WMXnN3LDK+2ZPO3pYZj27PbN9YP9u9rbWmnP5GlPD4O0Z2+Nr+rI2t86ac/kaU8PQ7TnrF19mnme62qPa6Q9k6c9PQzRngHvy/f12uX6aM/kaU8PA7Tn92p4CPfXPtdHeyZPe3oYoD07vGjGcmv/lI/2TJ729LB6ey6o2WF8o/a6NtozedrTw8rtedEOrlK4Hd+s/a6L9kye9vSwcnt2/+X1+b51fO14TbRn8rSnh1Xbc9Y2Lwu/fXfVntdEeyZPe3pYtT3buRnODn27dr0e2jN52tPDiu25tQaHtN5ffLRn8rSnhxXb88UaHNIJte/10J7J054eVmzP5TW4le9c/91LTzit/mMra72AqvZMnvb0sFp7blp6z/Wy/w/f80ePjT/v/PvrZ0vd/Nj0enyqDjLfv68pGtOeHlZrz+dqbpmr/0MNP+qo5Qd81L01uxa/VgeZb63ZYzO0p4fV2nNMzS12xMU1+mPvrkeWWOdFxO6rY8x3dk3RmPb0sFp7Pl9zC/3xL9bkk2z9+vSnanItPlEHmeueGqIx7elhpfZsec2wP67Bg235l9r7a3AtXlEHmef6mqEz7elhpfZ8usYWOaJeYz7Ull8/XbDdIC6uY8yzgWuXsXba08NK7dnqHfbDXut53JE1sMgLa24tfqUOcrh3nVMjdKY9PazUns/U2AJX19jhvl0Ti3y55tbiP9ZBDrevJmhNe3pYqT0n19h8+w96c/1gd9XMAl+ssfV4oI5yqPfW4/SmPT2s1J6Hamy+P6ypeV5YMwus+SI+t9VhDnbGz9XD9KY9PazSni2+SPqeGptr+XEfqKk1OX7e1e1PeF49SnPa08Mq7TmpphZY+mbV8rfIvldTa3P4hyJve189RHfa08Mq7fnVmprvOzU139k1Nd+ba2p9PvZLdajHXPUn9XP6054eVmnP8jshL/+c3uI3m35k7VeMf8SHnvhe6wl/ekX9kAnQnh5Wac8dNTXfd2tqvuV/r51SU+t10QWXfPjSj37kQy+p/2YatKeHVdqzr6bmu7Sm5vuzmprv7TUFO6c9PazSnk/W1HzLLz94e03Nd0RNwc5pTw+rtOfGmprvtJqa74M1Nd+1NQU7pz09rNKeo2tqgaWflzm2hub7bE3BzmlPD6u055yaWuD8GpvnnP9UQ/P9eY3BzmlPD6u0Z/brNTbf/TU1z8drZoG1XjyMidOeHlZqz2/U2AJH1dgc19fIAl+vMdg57elhpfb8Zo0tcFmNHW75p5r37PmLmoOd054eVmrPZ2tskXfX3KHe8p0aWOQrNQg7pz09rNSerX59WXQdnsVXDnzM92sOdkF7elipPb9VY4t9riYP8pf14EJ/VYOwC9rTw0rtmb225hZ7WU0+4S3/uR5abK2XTGXqtKeH1drz2zW3xJHfrtly9oF6YLFP1Cjshvb0sFp7vlBzS931xF0nzvn4Fm+uP+oVNQ27oT09rNae2ak1uNxln3746Xf/2VF/fezyTzM/buGtdWAbtKeHFdvz6hoc0v7/UjuH3dCeHlZsz/Kvsu/OD2rfsCva08OK7ZkdUZMD+ljtGnZFe3pYtT1H1eRw7q09w+5oTw+rtmf2OzU6mBfUjlf0Nyf916/V/+WpRXt6WLk9X67RodxW+92t/3bLi4/5wf0HzvzRvr70/c98751vOnvJvZmZIO3pYeX2zH63Zgdybu12V/77A3O/r3Hv+ffVAE8B2tPD6u25u2aHsfvPFZ718Pe+VDuZ48AxX7+wBrfrbS89/5i73nrUW+o/6UJ7eli9PbNn1PAgvlA73akbv1s7WOKBs2p4G376yLfXVnve/KYX1Q9pQXt6GKA9X6nhIXy19rlDJ324tl9u/3HbvP/o3ZfXFo959gX1czrQnh4GaM8W9wjciVN3+nfRo+7Z/m9ep93xt7XRMi+t6Sdce0M9xPhpTw9DtGf2kRpf1Wkn1Q534oa7auvt+R9b/w5zSY0+2RturAcZPe3pYZD2zH6/5lf0P2t3O/HwQ7Xxtv3l3tp0gfNr7mAHXlUPM3ba08Mw7fna8t1s01/X3nZi3u8oW3qwNp7rZ2roUK+txxk77elhmPbM7t7xbx+He2ftawees/z2pgsdV9vP8b7n1sxhXl0TjJz29DBQe2afrC127/21px24e9ffZL184dvmP1sTh9vvr64etKeHodoz+5PaZLd+/n21o+07b4Vftp614GXti+rxea6uGcZNe3oYrD2zh2ub3Xn/ztNzR226Oz/8X7Wbg11QD8/zypph3LSnh+HaM3vb52urXdjFaz0rv7E/9xXn59eDc/1UDTFq2tPDgO2ZHf2Z2mzHPlh72IEBPlM0Jz4X1kPz/WlNMWra08OQ7ZnNjqztdua0XVyyZ5CPMx4en2Uv97iBRhPa08Ow7ZldVxvuxKm7+DTzQJ+kPiw+e+uB+Z5fU4ya9vQwcHtmX7+/Nt22r+7iO1xDfYnjsPi8o34+32U1xahpTw9Dt2c2e/gbtfG2vGI3F80YLD2HxUd7JkB7ehi+PbPZN79Vm2/ptl1dpXDA9BwaH+2ZAO3pYR3tmR2/va+W37u7y8IPmp5D4qM9E6A9PaylPbPZt+86oXaxyP4f7PI+XAOn5+D4aM8EaE8Pa2rPI+6++d7ay+E+8YqLd3vj48HTc1B8tGcCtKeH9bXnEUd/6v21oyf7/l99uR7fhTWk58nx0Z4J0J4e1tqeR/zRC7/8xW8+8L03f+OUtx9x7Wf//FNf/4uv1CO7spb0PCk+2jMB2tPDutszrDWl54n4aM8EaE8PrdqztvT8OD7aMwHa00On9qwxPY/HR3smQHt6aNSetaan4qM9E6A9PfRpz07Ts7/+d9t+FB/tmQDt6aFNe3aSnm9d/uBLbpj97atOfOvSS4Ed6pH4aM8EaE8PXdqzg/R8/n/XNj/yNzu5c+CD2jMF2tNDk/bsID3nH3LX42s+Wg9sw4PaMwHa00OP9mw/PftPr02e5NX12DZcXf87n/a0oD09tGjP9tPzyv9TmxzkwXp0VdrTgvb00KE9O0jP82qTQwwUH+1pQXt6aNCe1dMzVHy0pwXt6WH87RkiPQPFR3ta0J4eRt+eYdIzTHy0pwXt6WHs7RkqPYPER3ta0J4eRt6e4dIzRHy0pwXt6WHc7RkyPQPER3ta0J4eRt2eYdOzeny0pwXt6WHM7Rk6PSvHR3ta0J4eRtye4dOzany0pwXt6WG87VlHelaMj/a0oD09jLY960nPavHRnha0p4extmdd6VkpPtrTgvb0MNL2rC89q8RHe1rQnh7G2Z51pmeF+GhPC9rTwyjbs9707D4+2tOC9vQwxvasOz27jo/2tKA9PYywPetPz27joz0taE8P42vPJtKzy/hoTwva08Po2rOZ9OwuPtrTgvb0MLb2bCo9u4qP9rSgPT2MrD2bS89u4qM9LWhPD+NqzybTs4v4aE8L2tPDqNqz2fTsPD7a04L29DCm9mw6PTuOj/a0oD09jKg9m0/PTuOjPS1oTw/jaU8iPTuMj/a0oD09jKY9mfTsLD7a04L29DCW9qTSs6P4aE8L2tPDSNqTS89O4qM9LWhPD+NoTzI9O4jPGbUBo6Y9PYyiPdn0bD8+/7fmGTXt6WEM7UmnZ9vx+USNM2ra08MI2pNPz3bjc1pNM2ra00O+PWNIzzbj86UaZtS0p4d4e8aRnu3F56GaZdS0p4d0e8aSnm3F54c1yqhpTw/h9ownPduJz1U1yahpTw/Z9owpPduIz7tqkFHTnh6i7RlXeraOzxtqjlHTnh6S7RlberaMj+9UtKA9PQTbM770zGZX1xHn054WtKeHXHvGmJ7ZO+qQ82lPC9rTQ6w9o0yP9kyB9vSQas8406M9U6A9PYTaM9L0aM8UaE8PmfaMNT3aMwXa00OkPaNNj/ZMgfb0kGjPeNOjPVOgPT0E2jPi9GjPFGhPD5tvz5jToz1ToD09bLw9o06P9kyB9vSw6faMOz3aMwXa08OG2zPy9GjPFGhPD5ttz9jToz1ToD09bLQ9o0+P9kyB9vSwyfaMPz3aMwXa08MG29MgPdozBdrTw+ba0yE92jMF2tPDxtrTIj3aMwXa08Om2tMjPdozBdrTw4ba0yQ92jMF2tPDZtrTJT3aMwXa08NG2tMmPdozBdrTwyba0yc92jMF2tPDBtrTKD3aMwXa08P629MpPdozBdrTw9rb0yo92jMF2tPDutvTKz3aMwXa08Oa29MsPdozBdrTw3rb0y092jMF2tPDWtvTLj3aMwXa08M629MvPdozBdrTwxrb0zA92jMF2tPD+trTMT3aMwXa08Pa2tMyPdozBdrTw7ra0zM92jMF2tPDmtrTND3aMwXa08N62tM1PdozBdrTw1ra0zY92jMF2tPDOtrTNz3aMwXa08Ma2tM4PdozBdrTw/Dt6Zwe7ZkC7elh8Pa0To/2TIH29DB0e3qnR3umQHt6GLg9zdOjPVOgPT0M257u6dGeKdCeHgZtT/v0aM8UaE8PQ7anf3q0Zwq0p4cB2zOB9GjPFGhPD8O1Zwrp0Z4p0J4eBmvPJNKjPVOgPT0M1Z5ppEd7pkB7ehioPRNJj/ZMgfb0MEx7ppIe7ZkC7elhkPZMJj3aMwXa08Oltazm21dTy00nPbN9daLzXVpTjJr29PDyWlbzHVdTS00oPbPj6kzn+3BNMWra08MltazmO7KmlplSemZH1qnOd0lNMWra08PLalnNd6CmlphUemYH6lzn+2BNMWra08PFtawWOLfGFppWes6tc13g4hpj1LSnh721rBa4t8YWmVZ6ZvfWyS6wt8YYNe3p4aJaVovcU3PzTSw999TJLnJRzTFq2tPDhbWsFrmz5uaaWHpmd9bZLnJhzTFq2tPEVbWuFnlPzc0xtfS8p852katqjnHTniaurIW1yP77avAwU0vPffvrdBe5sgYZN+1p4vRaWAudsiA+k0vPKXW6C51ek4yb9jRxRS2sxfbP/bNrcn9wbfVbz549V9Qo46Y9XVxbK2uJOw9/t2ti6blnq5eZH3FtzTJy2tPF8q9PlnsP+ZDhtNJz7haf63nM9r5YS5z2dHFNLa0tHDjyuH3veNx764db+8D/q03Gat9xRy7/IsWPXVNPGCOnPW1cX2uLpa6vp4ux0542rqvFxVLX1dPF2GlPG6+pxcVSr6mni7HTnj5eV6uLJV5XTxajpz193PD6Wl8s9Pob6sli9LSnkZtrgbHQzfVUMX7a08hN23yX+anrwE31VDF+2tPJLbXEWOCWeqJoQHtaObXWGHOdWk8THWhPK+fVImOu8+ppogPt6eWNtcqY4431JNGC9jRzW60zDnNbPUX0oD3N3HpCrTQOccKt9RTRg/Z0c+KZtdY4yJkn1hNEE9rTzu212DjI7fX00IX29HNHrTae5I56cmhDexraxoVDn2qW3p+MUdKejvzmcwi/9TSkPS3d7gXnJznTaz0daU9PJ3qr/cdO8A5XS9rT1K0+ZFhu87menrSnLV+veJQvUnSlPX2d51vte0719dG2tKezW57iFxM74Ho9jWlPazfd/BS+hvPrb3aVws60p7kbnrJ3r3idy8L3pj3tvea6p+AdS6+/zn24utOeKbhm37W1Jp8Srt3nnusToD0TccXpV15VS3PSrrry9Cvqn0xv2jMhF1609+IPXvLhSy97wsl/d9VzH/rhB/7u5Prvti59+SUvu3jvRRfWP5X+tAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdI0B4gQXuABO0BErQHSNAeIEF7gATtARK0B0jQHiBBe4AE7QEStAdIeKQ9AJv2tKf9f+TAGSewXPsMAAAAAElFTkSuQmCC'" 

                />
                <div v-else class="mt-4 max-w-xs rounded border text-gray-400 hover:text-gray-500 border-gray-400 hover:border-gray-500 hover:cursor-pointer size-32 flex justify-center items-center" @click="openModal = true">
                  <PlusIcon class="size-8" />
                </div>
            </div>
            <div>
              <label for="itemName" class="block text-sm font-medium text-gray-700">Nombre del Item</label>
              <input v-model="currentItem.nombre" id="itemName" type="text" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
            </div>
            <div>
              <label for="itemCategory" class="block text-sm font-medium text-gray-700">Categoría</label>
              <select v-model="currentItem.tipo" id="itemCategory" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                <option value="bebida">Bebida</option>
                <option value="comida">Comida</option>
                <option value="paquete">Paquete</option>
                <option value="extras">Extras</option>
                <option value="almacen">Almacén</option>
              </select>
            </div>
            <div>
              <label for="itemDetalle" class="block text-sm font-medium text-gray-700">Detalle del item</label>
              <input v-model="currentItem.detalle" id="itemDetalle" type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
            </div>
            <div>
              <label for="itemQuantity" class="block text-sm font-medium text-gray-700">Cantidad</label>
              <input v-model.number="currentItem.cantidad" id="itemQuantity" type="number" required min="0"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
            </div>
            <div>
              <label for="itemPrice" class="block text-sm font-medium text-gray-700">Precio Unitario</label>
              <input v-model.number="currentItem.precio" id="itemPrice" type="number" required min="0" step="0.01"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50">
            </div>
            <div class="flex justify-end space-x-2">
              <button type="button" @click="resetForm"
                class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-gray-200 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                Cancelar
              </button>
              <button type="submit"
                class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                {{ editingItem ? 'Actualizar' : 'Agregar' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <div class="md:w-8/12 w-full">
        <!-- Buscador -->
        <div class="mb-4">
          <input v-model="searchTerm" type="text" placeholder="Buscar en el inventario..."
            class="w-full px-4 py-2 rounded-md border-gray-300 shadow-sm focus:border-green-300 focus:ring focus:ring-green-200 focus:ring-opacity-50"
            @input="searchInventory">
        </div>

        <!-- Pestañas de categorías -->
        <div class="mb-4">
          <nav class="flex space-x-4" aria-label="Tabs">
            <button v-for="category in categories" :key="category" @click="activeCategory = category" :class="[
              activeCategory === category
                ? 'bg-green-100 text-green-700'
                : 'text-gray-500 hover:text-gray-700',
              'px-3 py-2 font-medium text-sm rounded-md capitalize'
            ]">
              {{ category }}
            </button>
          </nav>
        </div>

        <!-- Tabla de inventario -->
        <div class="bg-white shadow rounded-lg overflow-scroll">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Nombre</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Categoría</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Detalle</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cantidad</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Precio</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Acciones</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in filteredInventory" :key="item.id" class="text-center">
                <td class="py-1 px-2 whitespace bg-gray-50">{{ item.nombre }}</td>
                <td class="py-1 px-2 whitespace-nowrap capitalize ">{{ item.tipo }}</td>
                <td class="py-1 px-2 whitespace bg-gray-50">{{ item.detalle }}</td>
                <td class="py-1 px-2 whitespace-nowrap">{{ item.cantidad }}</td>
                <td class="py-1 px-2 whitespace-nowrap bg-gray-50">${{ item.precio }}</td>
                <td class="py-1 px-2 whitespace-nowrap text-sm font-medium flex flex-col gap-1 place-items-center">
                  <button @click="editItem(item)"
                    class="bg-green-600 flex gap-1 w-full  items-center justify-center py-1 px-2 text-white rounded-lg hover:bg-green-900 mr-2">
                    <PencilIcon class="size-4" /> Editar
                  </button>
                  <button @click="deleteItem(item.id)"
                    class="bg-red-600 flex gap-1 py-1  items-center px-2 w-full justify-center text-white rounded-lg hover:bg-red-900">
                    <Trash2 class="size-4" /> Eliminar
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <ModalImages 
      :isOpen="openModal" 
      @close="openModal = false"
      @select-image="handleImageSelect"
      @upload-image="handleImageUpload"
  />
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import { PencilIcon, PlusIcon, Trash2 } from 'lucide-vue-next';
import ModalImages from './ModalImages.vue';

const { props } = usePage();
const inventory = ref(props.inventario)

const images = ref(props.imagenes);

const currentItem = reactive({
  id: null,
  nombre: '',
  imagen: '',
  detalle: '',
  tipo: '',
  cantidad: 0,
  precio: 0
})

const editingItem = ref(false)
const searchTerm = ref('')
const activeCategory = ref('Todos')
const openFileInput = ref(false)

const categories = computed(() => {
  const allCategories = inventory.value.map(item => item.tipo)
  return ['Todos', ...new Set(allCategories)]
})
const openModal = ref(false);

const handleImageSelect = (image) => {
  console.log('Imagen seleccionada:', image);
  currentItem.imagen = image.url;
  currentItem.imagen_ruta = image.ruta;
  // Hacer algo con la imagen seleccionada
};

const handleImageError = (event) => {
  event.target.src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAAAXNSR0IArs4c6QAAAi1JREFUSEuNlcFx3DAMRR8KsGvQFpG9Zl1IZuKrtwgnRdhXe8aFrH21i1jVYBcAL0iQAkVqEl0kUuL/wMcHJNRLAIV082d/t6za/fR6sBUx7fUANJ76b9B3B95H4kDAdIn9HvhtjIJaPqsg0/6scAu8rqj9c5FFiRx7gTkJeuhBly881RnhFo0EiUpdroDZEHAWmBRuanT/UAowWX60KtdDHyB7y6dIEYOPYYc6RiOkT5xgWKcPVPYxnUyQ5S/XA/DL919QjovJOlCXyGsQ6ldcpJ5LITXwuyYV5BH0uGHNtsgNQV4U9xeCT4QrlGy7LMeXwHVMNej67gADmzrBivQTuIKWALjuC7TdmLnI+X2+pRqkpweEu1APw30EjqU7LsX6o8JPlLfLCXs2HOunA/Bc3FUCChLViFKRffUCcvQ6GciTA5XzM9ni50uz2vMuEcQMBnVpa5xXDi4HUAP66xPA9m09BTN7J7tEi019VPT2tsNPmiWYBXZe8An0lMkrfLJ7HHbBpoMWFia0ylIlCNPXMjiBTD7HEvZmJ6+Cj5ov4EFAl3pC5ZwIvGH7TvasAkEAlxk0Fa84ZNATzcgJBKIxNUcYy9KVvsl3QJDzyzVYZlEri7Ab/CBGLmtmWpOBE6RZH3weNN90VyEyd5mbaqDRRSfQQz/IgqtX/+pR+I787H+9xkXhl9lWcWDaLewZTc12U9uh66UYcOQZ7I9Z2t3YB2vvDc5v5dK3fDn8DYAK8R2vUqjSAAAAAElFTkSuQmCC';
};

const handleImageUpload = (newImage) => {
  console.log('Nueva imagen subida:', newImage);
  images.value.push(newImage);
};



const filteredInventory = computed(() => {
  let filtered = inventory.value

  if (searchTerm.value) {
    filtered = filtered.filter(item =>
      item.nombre.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      item.tipo.toLowerCase().includes(searchTerm.value.toLowerCase())
    )
  }

  if (activeCategory.value !== 'Todos') {
    filtered = filtered.filter(item => item.tipo === activeCategory.value)
  }

  return filtered
})

const Toast = Swal.mixin({
  toast: true,
  position: "top-end",
  showConfirmButton: false,
  timer: 1500,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.onmouseenter = Swal.stopTimer;
    toast.onmouseleave = Swal.resumeTimer;
  }
});

const saveItem = () => {
  if (editingItem.value) {
    router.put(`/inventario/${currentItem.id}`, currentItem, {
      onSuccess: (response) => {
        Toast.fire({
          icon: "success",
          title: "Actualizado correctamente"
        });
        inventory.value = response.props.inventario
        resetForm();
      },
      onError: (errors) => {
        Toast.fire({
          icon: "error",
          title: "Hubo un problema al actualizar el ítem"
        });
      }
    });
  } else {
    router.post(`/inventario`, currentItem, {
      onSuccess: (response) => {
        Toast.fire({
          icon: "success",
          title: "Agregado correctamente"
        });
        inventory.value = response.props.inventario
        resetForm();
      },
      onError: (errors) => {
        Toast.fire({
          icon: "error",
          title: "Hubo un problema al agregar el ítem"
        });
      }
    });
  }
};

const editItem = (item) => {
  Object.assign(currentItem, item);
  editingItem.value = true;
}

const deleteItem = (id) => {
  Swal.fire({
    title: '¿Estás seguro?',
    text: "¡No podrás revertir esto!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#e66f23',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminarlo',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(`/inventario/${id}`, {
        onSuccess: (response) => {
          Toast.fire({
            icon: "success",
            title: "Eliminado correctamente"
          });
          inventory.value = response.props.inventario
        },
        onError: (errors) => {
          Toast.fire({
            icon: "error",
            title: "Hubo un problema al eliminar el ítem"
          });
        }
      });
    }
  });
}

const resetForm = () => {
  Object.assign(currentItem, {
    id: null,
    nombre: '',
    imagen: '',
    detalle: '',
    tipo: '',
    cantidad: 0,
    precio: 0
  });
  editingItem.value = false;
}


</script>