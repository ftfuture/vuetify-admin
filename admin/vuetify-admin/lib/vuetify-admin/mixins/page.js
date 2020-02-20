import { mapState, mapMutations } from "vuex";

export default {
  props: {
    title: [String, Function]
  },
  computed: {
    ...mapState({
      resource: state => state.api.resource,
      resourceName: state => state.api.resourceName
    }),
    getTitle() {
      return typeof this.title === "function"
        ? this.title(this.resource)
        : this.title;
    }
  },
  watch: {
    title: {
      handler() {
        this.setTitle(this.getTitle || this.defaultTitle);
      },
      immediate: true
    }
  },
  methods: {
    ...mapMutations({
      setTitle: "api/setTitle"
    })
  }
};