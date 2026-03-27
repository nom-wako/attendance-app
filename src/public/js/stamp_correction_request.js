const tabItems = document.querySelectorAll('.tab-list__item');

tabItems.forEach((tabItem) => {
  tabItem.addEventListener('click', () => {
    tabItems.forEach((t) => {
      t.classList.remove('is-active');
    });
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach((tabContent) => {
      tabContent.classList.remove('is-active');
    });

    tabItem.classList.add('is-active');
    const tabIndex = Array.from(tabItems).indexOf(tabItem);
    tabContents[tabIndex].classList.add('is-active');
  });
});
