document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    const examContainers = document.querySelectorAll('.se-exam-container');

    examContainers.forEach(container => {
        const widgetId = container.id.replace('se-exam-container-', '');
        const examData = window['se_exam_data_' + widgetId];

        if (examData) {
            new Exam(container, examData);
        }
    });

    class Exam {
        constructor(container, data) {
            this.container = container;
            this.data = data;
            this.currentQuestionIndex = 0;
            this.answers = {};
            this.globalTimerInterval = null;
            this.questionTimerInterval = null;

            this.renderIntro();
        }

        renderIntro() {
            this.container.innerHTML = `
                <h2>${this.data.title}</h2>
                <p>Duration: ${this.data.duration} minutes</p>
                <p>Number of questions: ${this.data.questions.length}</p>
                <button class="se-start-exam-btn">Start Exam</button>
            `;

            this.container.querySelector('.se-start-exam-btn').addEventListener('click', () => this.startExam());
        }

        startExam() {
            this.renderQuestionView();
            this.startGlobalTimer();
        }

        renderQuestionView() {
            this.container.innerHTML = `
                <div class="se-exam-header">
                    <div class="se-exam-title">${this.data.title}</div>
                    <div class="se-global-timer">Time Left: <span></span></div>
                </div>
                <div class="se-question-container"></div>
                <div class="se-exam-footer">
                    <button class="se-prev-question-btn">Previous</button>
                    <button class="se-next-question-btn">Next</button>
                    <button class="se-submit-exam-btn">Submit</button>
                </div>
            `;

            this.questionContainer = this.container.querySelector('.se-question-container');
            this.globalTimerDisplay = this.container.querySelector('.se-global-timer span');

            this.container.querySelector('.se-prev-question-btn').addEventListener('click', () => this.prevQuestion());
            this.container.querySelector('.se-next-question-btn').addEventListener('click', () => this.nextQuestion());
            this.container.querySelector('.se-submit-exam-btn').addEventListener('click', () => this.submitExam());

            this.showQuestion(this.currentQuestionIndex);
        }

        showQuestion(index) {
            if (index < 0 || index >= this.data.questions.length) {
                return;
            }

            this.currentQuestionIndex = index;
            const question = this.data.questions[index];

            let optionsHtml = '';
            if (question.options) {
                optionsHtml = '<ul class="se-options">';
                question.options.forEach((option, i) => {
                    optionsHtml += `
                        <li>
                            <label>
                                <input type="radio" name="question_${question.id}" value="${i}">
                                ${option.text}
                            </label>
                        </li>
                    `;
                });
                optionsHtml += '</ul>';
            }

            this.questionContainer.innerHTML = `
                <div class="se-question-header">
                    <h3>${question.title}</h3>
                    <div class="se-question-timer">Time Left: <span></span></div>
                </div>
                <div class="se-question-content">${question.content}</div>
                ${optionsHtml}
            `;

            this.startQuestionTimer(question.timer);
            this.updateNavButtons();
        }

        startGlobalTimer() {
            let duration = this.data.duration * 60;
            this.globalTimerInterval = setInterval(() => {
                duration--;
                const minutes = Math.floor(duration / 60);
                const seconds = duration % 60;
                this.globalTimerDisplay.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

                if (duration <= 0) {
                    clearInterval(this.globalTimerInterval);
                    this.submitExam();
                }
            }, 1000);
        }

        startQuestionTimer(duration) {
            clearInterval(this.questionTimerInterval);
            if (!duration) return;

            const timerDisplay = this.questionContainer.querySelector('.se-question-timer span');
            this.questionTimerInterval = setInterval(() => {
                duration--;
                timerDisplay.textContent = `${duration}s`;

                if (duration <= 0) {
                    this.nextQuestion();
                }
            }, 1000);
        }

        prevQuestion() {
            this.saveAnswer();
            if (this.currentQuestionIndex > 0) {
                this.showQuestion(this.currentQuestionIndex - 1);
            }
        }

        nextQuestion() {
            this.saveAnswer();
            if (this.currentQuestionIndex < this.data.questions.length - 1) {
                this.showQuestion(this.currentQuestionIndex + 1);
            } else {
                this.submitExam();
            }
        }

        updateNavButtons() {
            const prevBtn = this.container.querySelector('.se-prev-question-btn');
            const nextBtn = this.container.querySelector('.se-next-question-btn');

            prevBtn.disabled = this.currentQuestionIndex === 0;
            if (this.currentQuestionIndex === this.data.questions.length - 1) {
                nextBtn.style.display = 'none';
            } else {
                nextBtn.style.display = 'inline-block';
            }
        }

        saveAnswer() {
            const question = this.data.questions[this.currentQuestionIndex];
            const selectedOption = this.questionContainer.querySelector(`input[name="question_${question.id}"]:checked`);
            if (selectedOption) {
                this.answers[question.id] = selectedOption.value;
            }
        }

        submitExam() {
            this.saveAnswer();
            clearInterval(this.globalTimerInterval);
            clearInterval(this.questionTimerInterval);

            // For now, just log the answers.
            // Later, this will be an AJAX call to the backend for grading.
            console.log('Submitting answers:', this.answers);

            this.container.innerHTML = '<h2>Exam Submitted!</h2><p>Thank you for completing the exam. Your results will be available shortly.</p>';
        }
    }
});
