<template>
	<div @click.prevent="setFocus()" class="border bg-gray-900 cursor-text flex-1 h-64 overflow-y-auto rounded shadow-md text-gray-100 text-sm">
		<div ref="console" class="leading-none m-1">
			<div class="font-mono mt-1 text-sm" v-for="(line, l) in lines" :key="l">
				<div class="px-1 whitespace-pre-wrap" v-text="line.text" :style="'color:' + line.color"></div>
				<template v-if="line.choices">
					<div class="px-1 whitespace-pre-wrap text-white" v-for="(choice, c) in line.choices" :key="c">
						<span class="ml-2 mr-px" v-text="'[' + c + ']'"></span><span class="ml-px pl-px" v-text="choice"></span>
					</div>
				</template>
			</div>
			<div class="mt-1">
				<div class="flex pb-1">
					<div class="pl-1">
						<span class="text-blue-500">superadmin@xtenant </span>
						<span v-if="bShowPrefix && cmdPrefix" class="text-gray-400" v-text="cmdPrefix"></span>
						<span v-if="bShowInputPrefix && inputPrefix" class="mr-1 text-gray-400" v-text="inputPrefix"></span>
					</div>
					<div class="flex-1 px-1 relative">
						<div class="absolute flex items-center inset-0">
							<span class="whitespace-pre-wrap" v-text="commandText"></span>
							<span ref="caret" :class="{ 'bg-white': bFocused }" class="border h-full w-2"></span>
						</div>
						<div class="absolute flex items-center inset-0">
							<span class="text-transparent whitespace-pre-wrap" v-text="commandTextTillPosition"></span>
							<span ref="underscore" class="border-l-2 h-full text-transparent hidden"></span>
						</div>
						<input ref="input" @keypress="onStartedTyping()" @keypress.enter="execCommand()" @keyup="onStoppedTyping()" @focus="bFocused = true" @blur="bFocused = false" class="absolute bg-transparent inset-0 outline-none placeholder-gray-700 text-transparent w-full" type="text" name="command" value="" v-model="commandText" :placeholder="placeholder">
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		props: ['cmdUrl', 'cmdPrefix', 'inputPrefix'],
		data()
		{
			return {
				input: null,
				caret: null,
				bFocused: false,
				bTyping: false,
				bShowPrefix: true,
				bShowInputPrefix: false,
				placeholder: 'new',
				commandText: '',
				initialCommand: null,
				qHash: null,
				commandTextTillPosition: '',
				lines: [],
				outputedLines: [],
				iLine: 0,
				printSpeed: 500
			}
		},
		mounted ()
		{
			this.console = this.$refs.console;
			this.input = this.$refs.input;
			this.caret = this.$refs.caret;
			this.underscore = this.$refs.underscore;
			this.blinkCaret();
			this.fetchLines();
			this.setFocus();
		},
		methods: {
			setFocus()
			{
				this.input.focus();
			},
			blinkCaret()
			{
				if (this.bFocused) {
					if (this.input.selectionStart < this.commandText.length) {
						this.caret.classList.add('hidden');
						if (!this.bTyping) {
							this.underscore.classList.toggle('hidden');
						} else {
							this.underscore.classList.add('hidden');
						}
						this.commandTextTillPosition = this.commandText.substring(0, this.input.selectionStart);
					} else {
						this.underscore.classList.add('hidden');
						if (!this.bTyping) {
							this.caret.classList.toggle('hidden');
							//this.caret.classList.remove('bg-white');
						} else {
							this.caret.classList.remove('hidden');
							//this.caret.classList.add('bg-white');
						}
					}
				}
				setTimeout(() => {
					this.blinkCaret();
				}, 500);
			},
			onStartedTyping() {
				this.bTyping = true;
				if (!(this.input.selectionStart < this.commandText.length)) {
					this.caret.classList.remove('hidden');
					//this.caret.classList.add('bg-white');
				}
				//this.input.focus();
			},
			onStoppedTyping() {
				setTimeout(() => {
					this.bTyping = false;
				}, 500);
			},
			execCommand() {
				const data = {};
				if (this.bShowInputPrefix) {
					data.web_input = this.commandText;
					data.q_hash = this.qHash;
				} else {
					this.initialCommand = this.commandText;
				}
				
				data.cmd = this.initialCommand;
				axios.post(this.cmdUrl, data).then((data) => {
					const newLines = data.data;
					this.printSpeed = 1000/newLines.length;
					this.outputedLines = this.outputedLines.concat(newLines); 
					/* this.$nextTick(() => {
						console.log('>>> updated');
						this.console.scrollIntoView({block: 'end'});
					}); */
				});
			},
			fetchLines() {
				const nextLine = this.outputedLines[this.iLine];
				setTimeout(() => {
					if (nextLine) {
						this.lines.push(nextLine);
						this.iLine++;
						this.$nextTick(() => {
							this.console.scrollIntoView(false);
							if (nextLine.action && nextLine.action == 'ask') {
								this.bShowPrefix = false;
								this.bShowInputPrefix = true;
								this.placeholder = nextLine.placeholder || '';
								this.qHash = nextLine.q_hash || null;
							} else {
								this.bShowInputPrefix = false;
								this.bShowPrefix = true;
							}
							this.commandText = '';
						});
					}
					this.fetchLines();
				}, this.printSpeed);
			}
		},
		/* computed: {
			lines() {
				return this.lines;
			}
		} */
	}
</script>
