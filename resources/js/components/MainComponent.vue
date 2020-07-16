<template>
    <v-flex>
        <v-row class="d-flex justify-content-center">
            <v-col cols="8" sm="12">
                <v-card
                    class="mx-auto"
                >
                    <v-card-title class="blue darken-4 text-center white--text">
                        Let's Play 😉
                    </v-card-title>

                    <v-card-text>
                        <v-container id="score" class="mt-5">
                            <h4 v-if="message" v-html="message" class="text-center mb-5"></h4>
                            <h5 class="text-center font-weight-bold">Score</h5>
                            <v-row class="d-flex justify-content-center">
                                <v-col cols="6">
                                    <h2 class="text-center">{{ user }}</h2>
                                    <p class="text-center">Player</p>
                                </v-col>
                                <v-col cols="6">
                                    <h2 class="text-center">{{ system }}</h2>
                                    <p class="text-center">System</p>
                                </v-col>
                            </v-row>
                            <v-row v-if="answer" class="d-flex justify-content-center mb-5">
                                <v-col cols="8">
                                    <table class="table table-responsive-md table-borderless">
                                        <tr>
                                            <td>
                                                <strong>Player’s hand:</strong>
                                            </td>
                                            <td class="text-left">
                                                {{ userCards }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <strong>Generated Hand:</strong>
                                            </td>
                                            <td class="text-left">
                                                {{ systemCards }}
                                            </td>
                                        </tr>
                                    </table>
                                </v-col>
                            </v-row>
                        </v-container>
                        <v-form v-model="valid">
                            <v-container>
                                <v-row class="d-flex justify-content-center">
                                    <v-col
                                        cols="12"
                                        md="12"
                                    >
                                        <v-text-field
                                            v-model="name"
                                            :rules="nameRules"
                                            :counter="24"
                                            label="Enter Your Name"
                                            required
                                        ></v-text-field>
                                    </v-col>
                                    <v-col
                                        cols="12"
                                        md="12"
                                    >
                                        <v-text-field
                                            v-model="card"
                                            :rules="cardRules"
                                            label="Enter Your Cards"
                                            @keyup="setCard"
                                            required
                                        ></v-text-field>
                                    </v-col>
                                    <v-col
                                        class="mx-auto"
                                        cols="4"
                                        md="4"
                                    >
                                        <v-btn block color="success" :disabled="enable" @click="getScores">Play</v-btn>
                                    </v-col>
                                </v-row>
                            </v-container>
                        </v-form>
                    </v-card-text>
                </v-card>

                <v-card
                    class="mx-auto mt-10"
                >
                    <v-card-title class="blue darken-4 text-center white--text">
                        Leaderboard 🏆
                    </v-card-title>

                    <v-card-text>
                        <v-simple-table v-if="scores.length">
                            <template v-slot:default>
                                <thead>
                                <tr>
                                    <th class="text-left">Player</th>
                                    <th class="text-center">Total Games</th>
                                    <th class="text-center">Wins</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="item in scores" :key="item.id">
                                    <td class="text-left">{{ item.name }}</td>
                                    <td class="text-center">{{ item.games }}</td>
                                    <td class="text-center">{{ item.wins }}</td>
                                </tr>
                                </tbody>
                            </template>
                        </v-simple-table>
                        <h5 v-else class="p-5 m-5 text-center">No one played yet!</h5>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </v-flex>
</template>

<script>
    export default {
        data() {
            return {
                userCards: "",
                systemCards: "",
                system: 0,
                user: 0,
                scores: [],
                message: "",
                valid: false,
                enable: false,
                answer: false,
                name: '',
                nameRules: [
                    v => !!v || 'Name is required',
                    v => v.length <= 24 || 'Name must be less than 24 characters',
                ],
                card: '',
                cardRules: [
                    // v => !!v || 'Card is required',
                ],
                inputArrayValues: []
            }
        },
        mounted() {
            this.getScoresTable();
        },
        methods: {
            // get leaderboard data;
            getScoresTable() {
                axios.get('api/scores')
                    .then((res) => {
                        this.scores = res.data.data
                    })
                    .catch((err) => console.log("ERR", err));
            },
            setCard(e) {
                let inputKey = e.key.toString().toUpperCase(); // get keyboard input key value
                let inputValue = e.target.value.toString().toUpperCase(); // get input value

                if (inputKey === 'ENTER') {
                    this.getScores();
                } else {
                    this.getValidation(inputKey, inputValue);
                }
            },
            // validation api on each card entered by the user
            getValidation(inputKey, inputValue) {
                axios.post('api/input/validate', {
                    key: inputKey,
                    input_value: inputValue
                })
                    .then((res) => {
                        if (res.data.error) {
                            this.cardRules = ["Invalid input"]
                        } else {
                            this.card = res.data.data;
                            this.cardRules = []
                        }
                        this.message = '';
                    })
                    .catch((err) => {
                        console.log("ERR", err)
                    });
            },
            // api call to get scores of the each play
            getScores() {
                axios.post('api/input/generate', {
                    name: this.name
                })
                    .then((res) => {
                        if (!res.data.error) {
                            this.user = res.data.data.player;
                            this.system = res.data.data.system;
                            this.userCards = res.data.data.hands.player;
                            this.systemCards = res.data.data.hands.system;
                            this.answer = true;
                            this.message = res.data.data.status ? `<span class="green--text">You Won!</span>` : `<span class="red--text">Hard Luck, Try Again!</span>`;
                            this.getScoresTable();
                        }
                        setTimeout(() => {
                            this.message = "";
                        }, 5000)
                    })
                    .catch((err) => {
                        if (!!err.response.data.errors && !!err.response.data.errors.name) {
                            this.message = `<span class="red--text">Please enter your name!</span>`;
                        }
                    });
            },
        }
    }
</script>
