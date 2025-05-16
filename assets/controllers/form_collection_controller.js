import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ["collectionContainer"];

  static values = {
    index: Number,
    prototype: String,
  };

  connect() {
    // this.updateAvailableDays();
  }

  addCollectionElement() {
    //TODO: przerób na jquery

    const rowHtml = this.prototypeValue.replace(/__name__/g, this.indexValue);
    const row = document.createElement("tr");
    row.innerHTML = rowHtml;
    this.collectionContainerTarget.appendChild(row);
    this.indexValue++;

    this.updateAvailableDays();
  }

  removeCollectionElement(event) {
    //TODO: przerób na jquery

    const button = event.currentTarget;
    const row = button.closest("tr");
    if (row) {
      row.remove();
    }
    this.indexValue--;

    this.updateAvailableDays();
  }

  updateAvailableDays() {
    const allDaySelects = document.querySelectorAll('select[name$="[day]"]');

    const selectedDays = Array.from(allDaySelects)
      .map((select) => select.value)
      .filter((val) => val !== "");

    allDaySelects.forEach((select) => {
      const currentValue = select.value;

      Array.from(select.options).forEach((option) => {
        if (option.value === "" || option.value === currentValue) {
          option.disabled = false;
        } else {
          option.disabled = selectedDays.includes(option.value);
        }
      });
    });
  }
}
