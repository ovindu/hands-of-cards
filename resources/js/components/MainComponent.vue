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
                                   <span v-for="(systemCard, index) in systemCards"
                                         :key="index">{{ systemCard.card }} </span>
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
                cards: [
                    {
                        card: "2",
                        value: 2
                    }, {
                        card: "3",
                        value: 3
                    }, {
                        card: "4",
                        value: 4
                    }, {
                        card: "5",
                        value: 5
                    }, {
                        card: "6",
                        value: 6
                    }, {
                        card: "7",
                        value: 7
                    }, {
                        card: "8",
                        value: 8
                    }, {
                        card: "9",
                        value: 9
                    }, {
                        card: "10",
                        value: 10
                    }, {
                        card: "J",
                        value: 11
                    }, {
                        card: "Q",
                        value: 12
                    }, {
                        card: "K",
                        value: 13
                    }, {
                        card: "A",
                        value: 14
                    }
                ], // cards default keys and values
                userCards: "",
                systemCards: "",
                system: 0,
                user: 0,
                scores: [],
                message: "",
                valid: false,
                enable: true,
                answer: false,
                name: '',
                nameRules: [
                    v => !!v || 'Name is required',
                    v => v.length <= 24 || 'Name must be less than 24 characters',
                ],
                card: '',
                cardRules: [
                    v => !!v || 'Card is required',
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
                let escapeKeys = ["BACKSPACE", "DELETE"];
                let inputKey = e.key.toString().toUpperCase(); // get keyboard input key value
                let inputValue = e.target.value.toString().toUpperCase(); // get input value
                this.enable = !this.name || !this.card;

                // escape backspace and delete key inputs and set user card null
                if (!escapeKeys.includes(inputKey)) {
                    // condition to determine if user tries to enter 10
                    if (inputKey !== "1") {
                        if (inputKey === "0" && inputValue.includes((inputValue.indexOf(inputKey) - 1).toString())) {
                            // pass 10 as key value if user enter value 1 and 0 respectively
                            this.validateInput("10", inputValue);
                        } else {
                            this.validateInput(inputKey, inputValue);
                        }
                    }
                } else {
                    // set input field and array empty
                    this.inputArrayValues = [];
                    this.card = '';
                }
            },
            validateInput(inputKey) {
                // validate if the entered input key or value exists in the default cards array
                if (!this.cards.some(card => card.card === inputKey)) {
                    // set validations
                    this.cardRules = ["Invalid input"]
                    this.enable = true;
                } else {
                    // remove validations
                    this.cardRules = [];
                    this.enable = false;
                    this.inputArrayValues.push(inputKey); // create an array of user entered cards
                    this.card = this.inputArrayValues.join(" "); // modify the input field values with spaces
                }
            },
            calculateScores() {
                let systemScore = 0;
                let userScore = 0;

                // calculate each one score by comparing the values
                this.inputArrayValues.forEach((_userCard, index) => {
                    let userCardValue = this.getCardValue(_userCard);
                    let systemCardValue = this.systemCards[index].value;
                    if (systemCardValue < userCardValue) {
                        userScore += 1;
                    } else if (systemCardValue > userCardValue) {
                        systemScore += 1;
                    } else {
                        return false;
                    }
                })

                this.user = userScore;
                this.system = systemScore;
            },
            getCardValue(userCard) {
                // return the value of given card key
                return this.cards.filter(_card => {
                    return _card.card === userCard;
                })[0].value
            },
            generateCard(length) {
                // generate random cards, first set random index to the default array then sort the array and return the array values with new sorted indexes
                return this.cards
                    .map((a) => ({sort: Math.random(), value: a}))
                    .sort((a, b) => a.sort - b.sort)
                    .map((a) => a.value)
                    .slice(0, length);
            },
            getScores() {
                this.userCards = this.card;
                this.systemCards = this.generateCard(this.inputArrayValues.length); // call to generate random cards which has a length of user entered cards
                this.answer = true;
                this.calculateScores()
                this.saveScores();
                this.getScoresTable();
            },
            saveScores() {
                //show alert message
                this.message = this.user > this.system ? `<span class="green--text">You Won!</span>` : `<span class="red--text">Hard Luck, Try Again!</span>`;
                // api call to save game score
                axios.post('api/scores', {
                    name: this.name,
                    user_score: this.user,
                    system_score: this.system,
                    status: this.user > this.system
                })
                    .then(() => {
                        setTimeout(() => {
                            this.message = "";
                        }, 5000)
                    })
                    .catch((err) => console.log("ERR", err));
            }
        }
    }
</script>
