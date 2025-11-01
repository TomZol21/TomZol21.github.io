// calculator.js
document.addEventListener("DOMContentLoaded", () => {
  const buttons = document.querySelectorAll(".button");
  const prevDisplay = document.getElementById("previousOperation");
  const currDisplay = document.getElementById("currentOperation");
  const historyDiv = document.getElementById("history");

  let currentInput = "";
  let previousInput = "";
  let operator = null;
  let resetNext = false;

  function updateDisplay() {
    prevDisplay.textContent = previousInput + (operator ? " " + operator : "");
    currDisplay.textContent = currentInput || "0";
  }

  function clearAll() {
    currentInput = "";
    previousInput = "";
    operator = null;
    resetNext = false;
    updateDisplay();
  }

  function appendNumber(num) {
    if (resetNext) {
      currentInput = "";
      resetNext = false;
    }
    if (num === "." && currentInput.includes(".")) return;
    currentInput += num;
    updateDisplay();
  }

  function chooseOperator(op) {
    if (currentInput === "" && previousInput === "") return;

    if (previousInput !== "") {
      if (currentInput !== "") calculate();
      operator = op;
      updateDisplay();
      return;
    }

    previousInput = currentInput || previousInput;
    operator = op;
    currentInput = "";
    updateDisplay();
  }

  function calculate() {
    if (!operator || currentInput === "" || previousInput === "") return;

    const prev = parseFloat(previousInput);
    const curr = parseFloat(currentInput);
    let result;

    switch (operator) {
      case "+": result = prev + curr; break;
      case "−": result = prev - curr; break;
      case "×": result = prev * curr; break;
      case "÷": result = curr === 0 ? "Błąd" : prev / curr; break;
      default: return;
    }

    // Zapisz do historii
    if (result !== "Błąd") {
      const entry = document.createElement("div");
      entry.textContent = `${previousInput} ${operator} ${currentInput} = ${result}`;
      historyDiv.prepend(entry);
    } else {
      const entry = document.createElement("div");
      entry.textContent = `${previousInput} ${operator} ${currentInput} = Błąd (dzielenie przez 0)`;
      historyDiv.prepend(entry);
    }

    currentInput = result.toString();
    previousInput = "";
    operator = null;
    resetNext = true;
    updateDisplay();
  }

  buttons.forEach(btn => {
    btn.addEventListener("click", () => {
      const value = btn.textContent.trim();

      if (!isNaN(value) || value === ".") {
        appendNumber(value);
      } else if (["+", "−", "×", "÷"].includes(value)) {
        chooseOperator(value);
      } else if (value === "=") {
        calculate();
      } else if (value === "C") {
        clearAll();
      }
    });
  });

  updateDisplay();
});
