<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    
    <style>
        table {
            border-color: rgb(126, 126, 129);
        }

        th {
            letter-spacing: 2px;
            text-align: left;
            background-color: #fffacd;
        }

        .highlight {
            background-color: #e99db4;
            color: red
        }
    </style>
</head>

<body>
    <!-- 添加 #app 元素，讓 Vue 能正確掛載 -->
    <div id="app">
        <!-- 篩選條件表單 -->
        <label>队名:</label>
        <input type="text" v-model="teamfilter" required placeholder="输入队名关键字">

        <label>赔率:</label>
        <input type="number" v-model="oddsfilter" required placeholder="输入赔率" step="0.01">
        <button @click="filterbutton">查询</button>
        <br>
        <label>阀值</label>
        <input type="number" v-model="threshold" required placeholder="输入阀值" step="0.01">
        <br>
        <button @click="buttonOneClick">API按钮</button>
        <button @click="loadMatches">ajax按钮</button>

        <table v-if="filteredMatches.length > 0" border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>比赛ID</th>
                    <th>编号</th>
                    <th>赛事</th>
                    <th>开始时间</th>
                    <th>客队</th>
                    <th>主队</th>
                    <th>负赔率</th>
                    <th>赢赔率</th>
                    <th>来源</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="match in filteredMatches" :key="match.id">
                    <td>@{{ match.id }}</td>
                    <td>@{{ match.eventid }}</td>
                    <td>@{{ match.number }}</td>
                    <td>@{{ match.event }}</td>
                    <td>@{{ match.gametime }}</td>
                    <td>@{{ match.away_team }}</td>
                    <td>@{{ match.home_team }}</td>
                    <td :class="{highlight :(threshold !== null && threshold!== '' && match.negative_odds >= threshold)}">
                        @{{ match.negative_odds ? parseFloat(match.negative_odds).toFixed(2) : 'N/A' }}</td>
                    <td :class="{highlight :(threshold !== null && threshold!== '' && match.winning_odds >= threshold)}">
                        @{{ match.winning_odds ? parseFloat(match.winning_odds).toFixed(2) : 'N/A' }}</td>
                    <td>@{{ match.data_Sources }}</td>
                </tr>
            </tbody>
        </table>

        <p v-if="filteredMatches.length === 0 && dataLoaded">沒有可顯示的比賽數據。</p>
    </div>

    <script>
        const app = Vue.createApp({
            data() {
                return {
                    matches: [],        //存放比賽數據
                    teamfilter: '',     //隊名篩選
                    oddsfilter: '',     //賠率篩選
                    threshold: '',       //閥值 
                    dataLoaded: true    //判斷是否加載
                };
            },
            computed: {// 根據 threshold 和其他條件進行篩選
                filteredMatches() {
                    // 如果沒有輸入 threshold，返回所有數據
                    if (!this.threshold) {
                        return this.matches;
                    } else {
                        // 根據 threshold 和賠率過濾數據
                        return this.matches.filter(match => {
                            return match.negative_odds >= this.threshold || match.winning_odds >= this.threshold;
                        })
                    };
                }
            },
            methods: {
                filterbutton() {
                    //清空錯誤訊息
                    this.errors = {};
                    // 初始化驗證標誌
                    let isValid = true;
                    // 解析並驗證賠率
                    let minOdds = parseFloat(this.oddsfilter);
                    if (isNaN(minOdds) || minOdds < 0 || minOdds > 99) {
                        console.log('賠率必須是0到99之間');
                        isValid = false;
                    }

                // 去除隊名首尾的空格
                let teamkeyword = this.teamfilter.trim();
                console.log(this.teamfilter);
                // 如果隊名不符合簡體中文
                // if (teamkeyword && typeof teamkeyword !== 'string') {
                //     console.log('隊名必須是字串');
                //     isValid = false;
                //     }
                // 驗證失敗時不發送請求
                    if (!isValid) {
                    return;
                    }

                // 驗證通過，發送請求
                    let minimumthreshold = parseFloat(this.threshold);
                    if (isNaN(minimumthreshold)) {
                        minimumthreshold = 0;
                    }

                   this.loadMatches(minOdds, teamkeyword, minimumthreshold);
                },

                async buttonOneClick() {
                try {
                  const response = await fetch('/load-matches');
                  if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                 this.matches = data;
             } catch (error) {
                   console.error('API 请求失败:', error);
                    }
                  },

                loadMatches(minOdds, teamkeyword, minimumthreshold) {
                    try{
                        console.log('發送的隊名篩選:', teamkeyword);  // 確認是否正確發送隊名數據

                        $.ajax({
                            url: '/load-matches',
                            type: 'POST',
                            data: {
                                table: 'events',
                                minOdds: minOdds,
                                teamkeyword: teamkeyword,
                                minimumthreshold: minimumthreshold
                            },
                            success: (data) => {
    console.log('返回的數據:', data); // 打印返回的數據
    if (Array.isArray(data)) {
        this.matches = data;
    } else {
        console.error('返回的數據格式不正確', data);
    }
},
                            error: (xhr, status, error) => {
                                console.error('AJAX 請求失敗:', status, error);
                                console.error('HTTP狀態碼')
                            }
                        });
                    } catch (error) {
                        console.error('請求失敗:', error);
                    }
                }
            },
            mounted() {
                // this.loadMatches(0, '', 0);
            }
        });

        app.mount('#app');
    </script>
</body>

</html>