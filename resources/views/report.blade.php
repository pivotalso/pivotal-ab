<!doctype html>
<html>
<head>
    <title>Laravel AB Report</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="min-h-full">
    <nav class="border-b border-gray-200 bg-white flex justify-between">
        <div class="w-full pl-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex">
                        <div class="flex flex-shrink-0 items-center">
                            AB
                        </div>
                        <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                            <!-- Current: "border-indigo-500 text-gray-900", Default: "border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700" -->
                            <a href="/" class="border-indigo-500 text-gray-900 inline-flex items-center border-b-2 px-1 pt-1 text-sm font-medium" aria-current="page">Reports</a>
                        </div>
                    </div>
                    <div class="-mr-2 flex items-center sm:hidden">

                    </div>
                </div>
            </div>

            <!-- Mobile menu, show/hide based on menu state. -->
            <div class="sm:hidden" id="mobile-menu">
                <div class="space-y-1 pb-3 pt-2">
                    <!-- Current: "border-indigo-500 bg-indigo-50 text-indigo-700", Default: "border-transparent text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800" -->
                    <a href="/" class="border-indigo-500 bg-indigo-50 text-indigo-700 block border-l-4 py-2 pl-3 pr-4 text-base font-medium" aria-current="page">Reports</a>
                </div>
            </div>
        </div>
        <div class="px-4 flex items-center justify-center text-nowrap text-xs">
            Close browser to logout
        </div>
    </nav>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" x-data="data()">
        <header>
            <div class="py-10">
                <h1 x-show="!current" class="text-3xl font-bold leading-tight tracking-tight text-gray-900">Experiments</h1>
                <h1 x-show="current" class="text-3xl font-bold leading-tight tracking-tight text-gray-900">Experiments :: <span x-text="current.name"></span></h1>
            </div>
        </header>
        <main>
            <div>
               <div class="flex">
                   <div class="w-1/3 flex flex-col ">
                       <ul role="list" class="divide-y divide-gray-200 w-full">
                           <template x-for="(exp, index) in experiments" :key="index">
                           <li class="py-4 px-8" >
                               <a :href="getUrl(exp)" class="border-l-4 border-teal-500 hover:bg-gray-100 py-2 px-4 w-full" x-text="exp.name"></a>
                           </li>
                           </template>
                       </ul>
                   </div>
                   <div class="w-full">
                       <hr>

                       <div x-show="current" class=" border-gray-200 border-l border-gap-2 p-4">
                           <div class="flex gap-2">
                               <button @click="tab = 'charts'" class="bg-gray-200 px-4 py-2 rounded-lg" :class="{'bg-teal-300': tab === 'charts'}">Charts</button>
                               <button @click="tab = 'data'" class="bg-gray-200 px-4 py-2 rounded-lg" :class="{'bg-teal-300': tab === 'data'}">Data</button>
                           </div>
                           <div x-show="tab === 'charts'">
                               <div class="flex flex-wrap gap-10" x-if="current">
                                   <div class="h-[200] w-[200] bg-gray-50 rounded-lg px-4 pb-6 pt-2 shadow">
                                       <h1>How often shown %</h1>
                                       <hr class="my-2">
                                       <canvas id="distribution" width="250" height="250"></canvas>
                                   </div>
                                   <div class="h-[200] w-[200] bg-gray-50 rounded-lg px-4 pb-6 pt-2 shadow">
                                       <h1>Reached goals %</h1>
                                       <hr class="my-2">
                                       <canvas id="total" width="250" height="250"></canvas>
                                   </div>
                                   <div class="h-[200] w-[200] bg-gray-50 rounded-lg px-4 pb-6 pt-2 shadow">
                                       <h1>Conversion rate %</h1>
                                       <hr class="my-2">
                                       <canvas id="goals" width="250" height="250"></canvas>
                                   </div>
                                   <template x-for="(condition,cid) in current.conditions" :key="cid">
                                       <div class="h-[200] w-[200] bg-gray-50 rounded-lg px-4 pb-6 pt-2 shadow">
                                           <h1>Shown vs goal: <span x-text="condition.condition"></span></h1>
                                           <hr class="my-2">
                                           <canvas :id="$id('cid')" width="250" height="250"></canvas>
                                       </div>
                                   </template>
                               </div>
                           </div>
                           <div x-show="tab === 'data'" class="py-4">
<pre class="code-block bg-slate-800 text-white p-8 rounded-lg">
{{ json_encode($experiment, JSON_PRETTY_PRINT) }}
</pre>
                           </div>
                       </div>
                       <div x-show="!current" class="text-center">
                           <h1 class="my-48">Awaiting events</h1>
                       </div>
                   </div>

               </div>

            </div>

        </main>
    </div>
</div>

<script type="text/javascript">
    function data() {
        return {
            tab: 'charts',
            init() {
                this.renderChart();
            },
            getUrl(experiment) {
                return `{{$path}}/${experiment.id}`
            },
            renderChart() {
                const hits = this.current.conditions.reduce((acc, row) => acc + row.hits, 0);
                const data = [
                    ...this.current.conditions
                ];
                new Chart(document.getElementById('distribution'),
                    {
                        type: 'pie',
                        data: {
                            labels: data.map(row => row.condition),
                            datasets: [
                                {
                                    label: 'View %',
                                    data: data.map(row => ((row.hits / hits) * 100).toFixed(2)),
                                }
                            ]
                        }
                    }
                );
                new Chart(document.getElementById('total'),
                          {
                              type: 'pie',
                          data: {
                              labels: data.map(row => row.condition),
                                  datasets: [
                                      {
                                          label: 'Goal #',
                                          data: data.map(row => row.goals),
                                      }
                              ]
                          }
                  }
                );
                new Chart(document.getElementById('goals'),
                      {
                          type: 'pie',
                          data: {
                              labels: data.map(row => row.condition),
                                  datasets: [
                                      {
                                          label: 'Converstion %',
                                          data: data.map(row => row.conversion),
                                      }
                              ]
                          }
                  }
                );
                this.$nextTick(() => {
                      this.current.conditions.map((r,i) => {
                              const id = `cid-${i + 1}`
                                  new Chart(document.getElementById(id),
                                          {
                                              type: 'pie',
                                          data: {
                                              labels: ['hits', 'goals'],
                                                  datasets: [
                                                      {
                                                          label: r.condition,
                                                          data: [r.hits, r.goals],
                                                      }
                                              ]
                                          }
                                  }
                              );
                          })
                  });
            },
            experiments: {!! json_encode($experiments) !!},
            @if (!empty($experiment))
current: {!! json_encode($experiment) !!},
            @else
current: null
            @endif
}
    }
</script>
</body>
</html>
